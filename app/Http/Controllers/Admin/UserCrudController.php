<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Dick\CRUD\Http\Controllers\CrudController;
use Illuminate\Http\Request;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\UserCreateRequest as StoreRequest;
use App\Http\Requests\UserUpdateRequest as UpdateRequest;

class UserCrudController extends CrudController {

	public $crud = array(
						"model" => "App\User",
						"entity_name" => "user",
						"entity_name_plural" => "users",
						"route" => "admin/user",

						// *****
						// COLUMNS
						// *****
						//
						// Define the columns for the table view as an array:
						//
						"columns" => [
											[
												'name' => 'name',
												'label' => "Name surname"
											],
											[
												'name' => 'email',
												'label' => "Email"
											],
											[
												// n-n relationship (with pivot table)
												'label' => "Roles",
												'type' => 'select_multiple',
												'name' => 'roles', // the method that defines the relationship in your Model
												'entity' => 'roles', // the method that defines the relationship in your Model
												'attribute' => 'display_name', // foreign key attribute that is shown to user
												'model' => "App\Role", // foreign key model
												'pivot' => true, // on create&update, do you need to add/delete pivot table entries?
											],
									],
						//
						// or as a string:
						//
						// "columns" => "title,slug,content",


						// *****
						// CREATE FIELDS
						// *****
						//
						"create_fields" => [
												[
													'name' => 'name',
													'label' => 'Name Surname',
													'type' => 'text',
												],
												[
													'name' => 'email',
													'type' => 'email',
													'label' => "Email address"
												],
												[
													'name' => 'password',
													'type' => 'password',
													'label' => "Password"
												],
												[
													'name' => 'password_confirmation',
													'type' => 'password',
													'label' => "Confirm password"
												],
												[
													// n-n relationship (with pivot table)
													'label' => "Roles",
													'type' => 'select_multiple',
													'name' => 'roles', // the method that defines the relationship in your Model
													'entity' => 'roles', // the method that defines the relationship in your Model
													'attribute' => 'display_name', // foreign key attribute that is shown to user
													'model' => "App\Role", // foreign key model
													'pivot' => true, // on create&update, do you need to add/delete pivot table entries?
												],
											],

						// *****
						// UPDATE FIELDS
						// *****
						//
						"update_fields" => [
												[
													'name' => 'name',
													'label' => 'Name Surname',
													'type' => 'text',
												],
												[
													'name' => 'email',
													'type' => 'email',
													'label' => "Email address"
												],
												[
													'name' => 'change_password',
													'type' => 'password',
													'label' => "Change password"
												],
												[
													'name' => 'change_password_confirmation',
													'type' => 'password',
													'label' => "Confirm change password"
												],
												[
													// n-n relationship (with pivot table)
													'label' => "Roles",
													'type' => 'select_multiple',
													'name' => 'roles', // the method that defines the relationship in your Model
													'entity' => 'roles', // the method that defines the relationship in your Model
													'attribute' => 'display_name', // foreign key attribute that is shown to user
													'model' => "App\Role", // foreign key model
													'pivot' => true, // on create&update, do you need to add/delete pivot table entries?
												],
											],
						//
						// or as a string:
						//
						// "fields" => "title,content",
						);

	public function store(StoreRequest $request)
	{
		return parent::storeCrud();
	}

	/**
	 * Update the specified resource in the database.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(UpdateRequest $request = null)
	{
		// if edit_permission is false, abort
		if (isset($this->crud['edit_permission']) && !$this->crud['edit_permission']) {
			abort(403, 'Not allowed.');
		}

		$model = $this->crud['model'];
		$this->prepareFields($model::find(\Request::input('id')));

		$all_variables = \Request::all();

		// if the change_password field has been filled, change the password
		if ($all_variables['change_password'] != '')
		{
			$all_variables['password'] = $all_variables['change_password'];
		}

		$item = $model::find(\Request::input('id'))
						->update(parent::compactFakeFields($all_variables));

		// if it's a relationship with a pivot table, also sync that
		foreach ($this->crud['fields'] as $k => $field) {
			if (isset($field['pivot']) && $field['pivot']==true)
			{
				$model::find(\Request::input('id'))->$field['name']()->sync(\Request::input($field['name']));
			}
		}

		// show a success message
		\Alert::success(trans('crud.update_success'))->flash();

		return \Redirect::to($this->crud['route']);
	}
}
