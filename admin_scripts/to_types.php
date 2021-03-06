<?php

class To_typesController  extends CmsGenerator {
	
	public function index() {
		$this->prepareIndexData();
		$this->render('to/types');
	}
	
	public function prepareIndexData(){
		
		$model_id = $this->request("model_id",0);
		if (!$model_id)
			$this->redirectUrl('/admintools/to_cars/');
			
		$this->view->title = $this->dataModel->getListTitle();
		$fields = $this->dataModel->getListFields();
		$fieldTitles = array();
		foreach ($fields as $fieldName=>$field) {
			$fieldTitles[$fieldName] = $this->dataModel->getFieldLabel($fieldName);
		}
		$this->view->fieldTitles = $fieldTitles;
		$this->view->addUrl = '/admintools/'.$this->modelName.'/add/?model_id='.(int)$model_id;
		$this->view->addTitle = $this->dataModel->getAddTitle();
		$listIds = $this->view->acl->getListIds($this->controller);
			
		$this->view->data = $this->model->select()->where("model_id=?",(int)$model_id)->fetchAll();

		$this->view->dataModel = $this->dataModel;
		$this->view->indexField = $this->dataModel->getIndexField();
		
		$car = $this->getCar($model_id);
		
		$this->addBreadCrumb($this->dataModel->getListTitle(),'/admintools/'.$this->dataModel->getModelName());
		$this->addBreadCrumb($car['CA'],'/admintools/to_models/?car_id='.$car['CAR_ID']);
		$this->addBreadCrumb($car['MO'],'/admintools/to_types/?model_id='.$car['MODEL_ID']);
	}
	
	private function getCar($id){
		$db = Register::get('db');
		$sql = "SELECT 
					CAR.id CAR_ID,MODEL.id MODEL_ID,CAR.NAME CA,MODEL.NAME MO 
				FROM `".DB_PREFIX."to_models` MODEL 
				LEFT JOIN `".DB_PREFIX."to_cars` CAR ON CAR.id=MODEL.car_id 
				WHERE MODEL.id='".(int)$id."';";
		return $db->get($sql);
	}
	
	public function save() {
		$form = $this->request('form');
		$indexField = $this->dataModel->getIndexField();
		$id = 0;
		if (!empty($form[$indexField]))
			$id = $form[$indexField];
		$form = $this->trimA($form);
		if (empty($id)){
			$this->model->insert($form);
		} else {
			$this->model->update($form,array($indexField => $id));
		}
		$this->redirect('index',$this->dataModel->getModelName(),'model_id='.(isset($form['model_id'])?$form['model_id']:''));
	}
	
	public function delete(){
		$db = Register::get('db');
		$indexField = $this->dataModel->getIndexField();
		$id = $this->request($indexField,0);
		
		$getCatById = ToModel::getTypeById($id);
		
		if (!empty($id)){
			$this->model->delete(array($indexField => $id));
			$db->post("DELETE FROM ".DB_PREFIX."to WHERE type_id='".(int)$id."';");
		}
		$this->redirect('index',$this->dataModel->getModelName(),'model_id='.$getCatById['model_id']);
	}
	
	public function delete_list(){
		$db = Register::get('db');
		$indexField = $this->dataModel->getIndexField();
		$ids = $this->request("delete_list",0);
		
		$getCatById = ToModel::getTypeById($ids[0]);
		
		if (!empty($ids)) {
			foreach ($ids as $id) {
				if (!empty($id)) {
					$this->model->delete(array($indexField => $id));
					$db->post("DELETE FROM ".DB_PREFIX."to WHERE type_id='".(int)$id."';");
				}
			}	
		}
		$this->redirect('index',$this->dataModel->getModelName(),'model_id='.$getCatById['model_id']);
	}
}

?>