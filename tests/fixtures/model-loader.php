<?php

use function PHPStan\Testing\assertType;

class Some_Model extends CI_Model
{
}

class Another_Model extends CI_Model
{
}

class Renamed_Model extends CI_Model
{
}

class SomeController extends CI_Controller
{
	public function index(): void
	{
		assertType('*ERROR*', $this->Some_Model);
		assertType('*ERROR*', $this->Another_Model);

		$this->load->model('Some_Model');
		assertType('Some_Model', $this->Some_Model);

		$this->load->model(Another_Model::class);
		assertType('Another_Model', $this->Some_Model);

		$this->load->model(Renamed_Model::class, 'renamedModel');
		assertType('Renamed_Model', $this->renamedModel);
	}
}
