<?php
	App::uses('InfinitasComponent', 'Libs.Controller/Component');

	class VoucherComponent extends InfinitasComponent {
		/**
		 * components being used here
		 */
		public $components = array();

		/**
		* The path to the voucher template
		*/
		public $path = '';

		public $voucher = 'gift-voucher.png';

		public $font = 'C:\xampp\php\extras\fonts\ttf\Vera.ttf';

		public $errors = null;

		public $voucherUuidCode = null;

		public $voucherSize = array();

		public $voucherRecource = null;

		/**
		 * Controllers initialize function.
		 */
		public function initialize(Controller $Controller) {
			$this->path = APP . 'extensions' . DS . 'libs' . DS . 'webroot' . DS . 'img' . DS;
			$this->voucher = $this->path . $this->voucher;
		}

		public function getVoucher() {
			if (!is_file($this->font)) {
				$this->errors[] = 'Font file missing';
				return false;
			}

			if ($this->__generateNewVoucher()) {

				if (
					$this->__writeVoucherCode() &&
					$this->__writeUserName() &&
					$this->__writeExpiryDate() &&
					$this->__writeTerms() &&
					$this->__writeVoucherTitle() &&
					$this->__writeVoucherDescription()
				) {
					$this->__saveVoucher();
				}
			}

			pr( $this->errors );
			exit;

			return $this->voucherUuidCode;
		}

		/**
		 * Creates a blank voucher for manipulation.
		 *
		 * @return boolean
		 */
		function __generateNewVoucher() {
			// get the size of the image
			$this->voucherSize	 = getimagesize($this->voucher);

			// create a new blank image
			$this->output		  = imagecreatetruecolor($this->voucherSize[0], $this->voucherSize[1]);

			// load the voucher template
			$this->voucherRecource = imagecreatefrompng($this->voucher);

			// copy the voucher to the png
			$copy = imagecopyresampled(
				$this->output,
				$this->voucherRecource,
				0, 0, 0, 0,
				$this->voucherSize[0],
				$this->voucherSize[1],
				$this->voucherSize[0],
				$this->voucherSize[1]
				);
			if ($copy) {
				return true;
			}

			$this->errors[] = 'Could not create the new voucher';
		}

		/**
		 * Write the code to the voucher
		 *
		 * @return boolean
		 */
		function __writeVoucherCode($color = array(255, 255, 0)) {
			$yellow = imagecolorallocate($this->output, $color[0], $color[1], $color[2]);
			$this->voucherUuidCode = md5(time());

			if (imagestring($this->output, 3, 500, 310, $this->voucherUuidCode, $yellow)) {
				return true;
			}

			$this->errors[] = 'Could not write the voucher code';
			return false;
		}

		/**
		 * Write the users name
		 *
		 * @return boolean
		 */
		function __writeUserName($userName = null, $color = array(255, 255, 0), $shadow = true) {
			if (!$userName) {
				$this->error[] = 'No User name passed. Using session name';
				$userName = $this->Controller->Session->read('Auth.User.username');

				if (!$userName) {
					$this->errors[] = 'No User in session';
					return false;
				}
			}

			$color = imagecolorallocate($this->output, $color[0], $color[1], $color[2]);

			// Add some shadow to the text
			if ($shadow) {
				imagettftext($this->output, 20, 0, 22, 35, imagecolorallocate($this->output, 128, 128, 128), $this->font, $userName);
			}

			//imagestring($this->output, 100, 20, 17, $userName, $color)
			if (imagettftext($this->output, 20, 0, 20, 33, $color, $this->font, $userName)) {
				return true;
			}

			$this->errors[] = 'Could not write the voucher code';
			return false;
		}

		/**
		 * Write the expiry date.
		 *
		 * Will default to a date 1 week from now if there is no date passed.
		 *
		 * @return boolean
		 */
		function __writeExpiryDate($date = null, $color = array(255, 255, 0)) {
			if ($date && $date < date('Y-m-d H:i:s')) {
				$this->__voidVoucher();
			}

			if (!$date) {
				$this->errors[] = 'No date passed. Using 1 week from now';
				$date = date('D, j \o\f F Y', mktime(0, 0, 0, date('m'), date('d') + 7, date('Y')));
			}

			imagettftext($this->output, 10, 0, 480, 32, imagecolorallocate($this->output, 0, 0, 0), $this->font, __d('libs', 'Expires:'));

			//imagestring($this->output, 100, 20, 17, $userName, $color)
			if (imagettftext($this->output, 10, 0, 550, 32, imagecolorallocate($this->output, $color[0], $color[1], $color[2]), $this->font, $date)) {
				return true;
			}

			$this->errors[] = 'Could not write the voucher code';
			return false;
		}

		/**
		 * Terms and conditions
		 *
		 * @param array $color the text collor for terms, defaults to black
		 * @return boolean
		 */
		function __writeTerms($color = array(0, 0, 0)) {
			$color = imagecolorallocate($this->output, $color[0], $color[1], $color[2]);

			$text = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, '.
				'sed do eiusmod tempor incididunt ut labore et dolore magna '.
				'aliqua. Ut enim ad minim veniam, quis nostrud exercitation '.
				'ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis '.
				'aute irure dolor in reprehenderit in voluptate velit esse '.
				'cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat '.
				'cupidatat non proident, sunt in culpa qui officia deserunt '.
				'mollit anim id est laborum.';

			return imagettftext($this->output, 10, 90, 30, 320, $color, $this->font, __d('libs', 'Terms and Conditions')) &&
			imagettftext($this->output, 6, 90, 50, 320, $color, $this->font, wordwrap(__d('libs', $text), 60, "\n"));
		}

		/**
		 * Voucher title
		 *
		 * @param array $color the text collor for terms, defaults to black
		 * @return boolean
		 */
		function __writeVoucherTitle($color = array(0, 146, 63), $shadow = true) {
			$color = imagecolorallocate($this->output, $color[0], $color[1], $color[2]);

			$heading = __d('libs', 'One free some product');

			if ($shadow) {
				imagettftext($this->output, 30, 0, 197, 97, imagecolorallocate($this->output, 142, 143, 44), $this->font, $heading);
			}

			return imagettftext($this->output, 30, 0, 195, 95, $color, $this->font, $heading);
		}

		/**
		 * Voucher description
		 *
		 * @param array $color the text collor for terms, defaults to black
		 * @return boolean
		 */
		function __writeVoucherDescription($color = array(0, 0, 0)) {
			$color = imagecolorallocate($this->output, $color[0], $color[1], $color[2]);

			$text = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, '.
				'sed do eiusmod tempor incididunt ut labore et dolore magna '.
				'aliqua. Ut enim ad minim veniam, quis nostrud exercitation '.
				'ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis '.
				'aute irure dolor in reprehenderit in voluptate velit esse '.
				'cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat '.
				'cupidatat non proident, sunt in culpa qui officia deserunt '.
				'mollit anim id est laborum.';

			return imagettftext($this->output, 8, 0, 195, 130, $color, $this->font, wordwrap(__d('libs', $text), 90, "\n"));
		}

		/**
		 * Void the voucher
		 *
		 * This can be called to void the voucher.  Will print big voids all over.
		 *
		 * @param string $text the void text to display
		 * @param array $color the rgb color of the text
		 * @return true if text is added, false if not.
		 */
		function __voidVoucher($text = 'Expired', $color = array(128, 128, 128)) {
			$color = imagecolorallocate($this->output, $color[0], $color[1], $color[2]);
			$text = __d('libs', $text);

			$return = imagettftext($this->output, 60, 45, 100, 300, $color, $this->font, $text) &&
			imagettftext($this->output, 60, 45, 300, 300, $color, $this->font, $text) &&
			imagettftext($this->output, 60, 45, 500, 300, $color, $this->font, $text);

			if (!$return) {
				$this->errors[] = 'Error adding some void text';
			}

			return $return;
		}

		/**
		 * Gets the data from the resource.
		 *
		 * from here it can be saved to file or output for download.
		 *
		 * @return mixed $return the raw image data.
		 */
		function __getImageData() {
			ob_start();
				imagepng($this->output);
			$return = ob_get_contents();
			ob_end_clean();

			return $return;
		}

		/**
		 * Save to file
		 *
		 * Saves the image to disk.
		 *
		 * @return boolean
		 */
		function __saveVoucher() {
			$newVoucher = $this->path.'test.png';

			App::import('File');
			$this->File = new File($newVoucher, true);

			$this->File->open('w');
			if ($this->File->write($this->__getImageData())) {
				return true;
			}

			$this->errors[] = 'Could not write the file';
			return false;
		}
	}