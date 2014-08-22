<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array();
    public  $components = array('FileUpload.FileUpload');
/**
 * Displays a view
 *
 * @param mixed What page to display
 * @return void
 * @throws NotFoundException When the view file could not be found
 *	or MissingViewException in debug mode.
 */
    function beforeFilter(){
        $this->FileUpload->allowedTypes(array(
                'jpg' => array('image/jpeg', 'image/pjpeg'),
                'jpeg' => array('image/jpeg', 'image/pjpeg'),
                'gif' => array('image/gif'),
                'png' => array('image/png','image/x-png'),
                'zip' => array('application/octet-stream')
            )
        );
        $this->FileUpload->fields(array('name'=> 'name', 'type' => 'type', 'size' => 'size')); //các field tương ứng trong csdl
        $this->FileUpload->uploadDir('files'); //file upload ảnh, lưu folder dã có sẵn
        $this->FileUpload->fileModel('Fileupload');  //model của csdl
        $this->FileUpload->fileVar('file'); //name input, mac dinh la file
    }
	public function display() {
		$path = func_get_args();

		$count = count($path);
		if (!$count) {
			return $this->redirect('/');
		}
		$page = $subpage = $title_for_layout = null;

		if (!empty($path[0])) {
			$page = $path[0];
		}
		if (!empty($path[1])) {
			$subpage = $path[1];
		}
		if (!empty($path[$count - 1])) {
			$title_for_layout = Inflector::humanize($path[$count - 1]);
		}
		$this->set(compact('page', 'subpage', 'title_for_layout'));

		try {
			$this->render(implode('/', $path));
		} catch (MissingViewException $e) {
			if (Configure::read('debug')) {
				throw $e;
			}
			throw new NotFoundException();
		}
	}
    function upload()
    {

        if(!empty($this->data)){
            if ($this->Fileupload->save($this->data)) {
                $this->Session->setFlash(__('Upload successfully', true));
                //$this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('This file could not be uploaded. Please, try again.', true));
            }
        }
    }
}
