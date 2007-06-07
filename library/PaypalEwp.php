<?php
/*
  paypalewp.php

  The PayPal class implements the dynamic encryption of PayPal "buy now"
  buttons using the PHP openssl functions. (This evades the ISP restriction
  on executing the external "openssl" utility.)

  Original Author: Ivor Durham (ivor.durham@ivor.cc)  Edited by PayPal_Ahmad  (Nov. 04, 2006)
  Posted originally on PDNCommunity:  http://www.pdncommunity.com/pdn/board/message?board.id=ewp&message.id=87#M87

  Doumented further by Joseph Holsten (joseph@josephholsten.com)
*/
  
class PayPalEWP {
    
    var $certificate;	// Certificate resource
    var $certificateFile;	// Path to the certificate file

    var $privateKey;	// Private key resource (matching certificate)
    var $privateKeyFile;	// Path to the private key file

    var $paypalCertificate;	// PayPal public certificate resource
    var $paypalCertificateFile;	// Path to PayPal public certificate file
    var $certificateID; // ID assigned by PayPal to the $certificate.

    var $tempFileDirectory;

    /**
    * Sets the ID assigned by PayPal to the client certificate
    *
    * @param integer $id The certificate ID assigned when the certificate was uploaded to PayPal
    * @return bool 
    * @access public
    */
    function setCertificateID($id) {
        $this->certificateID = $id;
    }

    /**
    * Set the client certificate and private key pair.
    *
    * @param string $certificateFilename The path to the client certificate
    * @param string $keyFilename The path to the private key corresponding to the certificate
    * @return bool TRUE if the private key matches the certificate.
    * @access public
    */
    function setCertificate($certificateFilename, $privateKeyFilename) {
        $result = false;

        if (is_readable($certificateFilename) && is_readable($privateKeyFilename))
        {
            $certificate=null;
            $handle=fopen($certificateFilename, "r");
            $size=filesize($certificateFilename);
            $certificate=fread($handle,$size);
            fclose($handle);

            $privateKey=null;              
            $handle=fopen($privateKeyFilename,"r");
            $size=filesize($privateKeyFilename);
            $privateKey=fread($handle, $size);
            fclose($handle);
        
      	    if (($certificate !== false) && ($privateKey !== false) && openssl_x509_check_private_key($certificate, $privateKey)) {
			    $this->certificate = $certificate;
			    $this->certificateFile = $certificateFilename;
			    $this->privateKey = $privateKey;
			    $this->privateKeyFile = $privateKeyFilename;
			    $result = true;
      	    }
        }

        return $result;
    }

    /**
    * Sets the PayPal certificate
    *
    * @param string $fileName The path to the PayPal certificate.
    * @param string $keyFilename The path to the private key corresponding to the certificate
    * @return bool TRUE iff the certificate is read successfully, FALSE otherwise.
    * @access public
    */
    function setPayPalCertificate($fileName) {
        if (is_readable($fileName)) {
            $handle=null;
            $certificate=null;
            $size=null;
            
            $handle=fopen($fileName, "r");
            if (!$handle){
                echo 'Paypal cert could not be opened';
            }
            $size=filesize($fileName);
        
            $certificate=fread($handle, $size);
            if (!$certificate){
                echo 'Paypal cert could not be read';
            }
            fclose($handle);

    	    if ($certificate !== false) {
			    $this->paypalCertificate = $certificate;
			    $this->paypalCertificateFile = $fileName;
			    return true;
    	    }
        }
        return false;
    }

    /**
    * Sets the directory into which temporary files are written.
    *
    * @param string $directory Directory in which to write temporary files.
    * @return bool TRUE iff directory is usable.
    * @access public
    */
    function setTempFileDirectory($directory) {
  	    if (is_dir($directory) && is_writable($directory)) {
    	    $this->tempFileDirectory = $directory;
      	    return true;
        } else {
      	    return false;
          }
      }

    /**
    * Using the previously set certificates and tempFileDirectory encrypt the button information.
    *
    * @param array $parameters Array with parameter names as keys.
    * @return string The encrypted string for the _s_xclick button form field.
    * @access public
    */
    function encryptButton($parameters) {
        // Check encryption data is available.

        if (($this->certificateID == '') || !isset($this->certificate) || !isset($this->paypalCertificate)) {
    	    return false;
        }

        $clearText = '';
        $encryptedText = '';

        // initialize data.
        $data = "cert_id=" . $this->certificateID . "\n";;
        foreach($parameters as $k => $v) 
            $d[] = "$k=$v";
            $data .= join("\n", $d);

        $dataFile = tempnam($this->tempFileDirectory, 'data');
        
        $out = fopen("{$dataFile}_data.txt", 'wb');
        fwrite($out, $data);
        fclose($out);
        
        $out=fopen("{$dataFile}_signed.txt", "w+"); 

        if (!openssl_pkcs7_sign("{$dataFile}_data.txt", "{$dataFile}_signed.txt", $this->certificate, $this->privateKey, array(), PKCS7_BINARY)) {
    	    return false;
        }
        fclose($out);

        $signedData = explode("\n\n", file_get_contents("{$dataFile}_signed.txt"));

        $out = fopen("{$dataFile}_signed.txt", 'wb');
        fwrite($out, base64_decode($signedData[1]));
        fclose($out);

        if (!openssl_pkcs7_encrypt("{$dataFile}_signed.txt", "{$dataFile}_encrypted.txt", $this->paypalCertificate, array(), PKCS7_BINARY)) {
    	    return false;
        }

        $encryptedData = explode("\n\n", file_get_contents("{$dataFile}_encrypted.txt"));

        $encryptedText = $encryptedData[1];

        @unlink($dataFile);  
        @unlink("{$dataFile}_data.txt");
        @unlink("{$dataFile}_signed.txt");
        @unlink("{$dataFile}_encrypted.txt");

        return $encryptedText;
    }
}
?>