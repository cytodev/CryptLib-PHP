<?php

    namespace io\cytodev\lib\cryptography;

    use io\cytodev\lib\cryptography\exceptions\CryptographyException;

    use io\cytodev\lib\cryptography\interfaces\ICryptographicPassword;
    use io\cytodev\lib\cryptography\interfaces\IMessageAuthenticationCode;

    use io\cytodev\lib\cryptography\meta\Cipher;

    /**
     * Class TwoWayAES
     *
     * @package io\cytodev\lib\cryptography
     */
    class TwoWayAES extends Cipher implements ICryptographicPassword, IMessageAuthenticationCode {

        /**
         * $password
         *
         * @var string
         */
        private $password = "";

        /**
         * $mac
         *
         * @var string
         */
        private $mac = "";

        /**
         * TwoWayAES constructor.
         *
         * @param string $cipher Cipher method [defaults: null]
         * @param string $iv     Initialization vector [defaults: null]
         *
         * @throws CryptographyException When no ciphers are available
         * @throws CryptographyException When $cipher is not available
         * @throws CryptographyException When $iv is not the correct length for
         *                               the current cipher
         */
        public function __construct(string $cipher = null, string $iv = null) {
            parent::__construct($cipher, $iv);
        }

        /**
         * @inheritdoc
         */
        public function setPassword(string $password): void {
            $this->password = $password;
        }

        /**
         * @inheritdoc
         */
        public function setMAC(string $mac): void {
            $this->mac = $mac;
        }

        /**
         * @inheritdoc
         */
        public function getMAC(): string {
            return $this->mac;
        }

        /**
         * encrypt
         *   Encrypts data
         *
         * @param string $data      The encrypted message to be decrypted
         * @param string &$tag      Authentication tag [defaults: null]
         * @param int    $tagLength Length of the authentication tag can be
         *                          between 4 and 16 for GCM mode [defaults: 16]
         *
         * @return string|bool
         */
        public function encrypt(string $data, string &$tag = null, int $tagLength = 16) {
            if(!$this->advanced)
                return openssl_encrypt($data, $this->cipher, $this->password, OPENSSL_RAW_DATA, $this->iv);

            return openssl_encrypt($data, $this->cipher, $this->password, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv, $tag, $this->mac, $tagLength);
        }

        /**
         * decrypt
         *   Decrypts data
         *
         * @param string $data The encrypted message to be encrypted
         * @param string $tag  Authentication tag [defaults: null]
         *
         * @throws CryptographyException When a tag is not supplied while in
         *                               advanced mode
         *
         * @return string|bool
         */
        public function decrypt(string $data, string $tag = null) {
            if(!$this->advanced)
                return openssl_decrypt($data, $this->cipher, $this->password, OPENSSL_RAW_DATA, $this->iv);

            if($tag === null)
                throw new CryptographyException("A tag should be provided when using AEAD mode");

            return openssl_decrypt($data, $this->cipher, $this->password, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv, $tag, $this->mac);
        }

    }
