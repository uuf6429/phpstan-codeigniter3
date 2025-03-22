<?php

use function PHPStan\Testing\assertType;

class SomeModel extends CI_Model
{

}

class SomeController extends CI_Controller
{
	public function index(): void
	{
		assertType('*ERROR*', $this->SomeModel);

		$this->load->model(SomeModel::class);

		assertType(SomeModel::class, $this->SomeModel);
	}
}
