<?php namespace Cartalyst\Sentry\Users\Eloquent;
/**
 * Part of the Sentry Package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Sentry
 * @version    2.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\Sentry\Users\ProviderInterface;
use Cartalyst\Sentry\Users\UserInterface;
use Cartalyst\Sentry\Users\UserNotActivatedException;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Cartalyst\Sentry\Groups\GroupInterface;

class Provider implements ProviderInterface {

	/**
	 * The Eloquent user model.
	 *
	 * @var string
	 */
	protected $model = 'Cartalyst\Sentry\Users\Eloquent\User';

	/**
	 * Create a new Eloquent User provider.
	 *
	 * @param  string  $model
	 * @return void
	 */
	public function __construct($model = null)
	{
		if (isset($model))
		{
			$this->model = $model;
		}
	}

	/**
	 * Finds a user by the given user ID.
	 *
	 * @param  mixed  $id
	 * @return Cartalyst\Sentry\UserInterface
	 */
	public function findById($id)
	{
		$model = $this->createModel();

		if ( ! $user = $model->newQuery()->find($id))
		{
			throw new UserNotFoundException;
		}

		return $user;
	}

	/**
	 * Finds a user by the login value.
	 *
	 * @param  string  $login
	 * @return Cartalyst\Sentry\UserInterface
	 */
	public function findByLogin($login)
	{
		$model = $this->createModel();

		if ( ! $user = $model->newQuery()->where($model->getLoginAttributeName(), '=', $login)->first())
		{
			throw new UserNotFoundException;
		}

		return $user;
	}

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  Cartalyst\Sentry\UserInterface  $user
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validateCredentials(UserInterface $user, array $credentials)
	{

	}

	/**
	 * Validates the users and throws a number of
	 * Exceptions if validation fails.
	 *
	 * @param  Cartalyst\Sentry\Users\UserInterface  $user
	 * @return bool
	 * @throws Cartalyst\Sentry\Users\LoginFieldRequiredException
	 * @throws Cartalyst\Sentry\Users\UserExistsException
	 */
	public function validate(UserInterface $user)
	{

	}

	/**
	 * Attempts to activate the given user by checking
	 * the activate code.
	 *
	 * @param  Cartalyst\Sentry\Users\UserInterface  $user
	 * @param  string  $activationCode
	 * @return bool
	 */
	public function validateActivate(UserInterface $user, $activationCode)
	{

	}

	/**
	 * Get a reset password code for the given user.
	 *
	 * @return string
	 */
	public function getResetPasswordCode(UserInterface $user)
	{

	}

	/**
	 * Attemps to reset a user's password by matching
	 * the reset code generated with the user's.
	 *
	 * @param  Cartalyst\Sentry\Users\UserInterface  $user
	 * @param  string  $resetCode
	 * @param  string  $newPassword
	 * @return bool
	 */
	public function attemptResetPassword(UserInterface $user, $resetCode, $newPassword)
	{

	}

	/**
	 * Wipes out the data associated with resetting
	 * a password.
	 *
	 * @param  Cartalyst\Sentry\Users\UserInterface  $user
	 * @return $user
	 */
	public function clearResetPassword(UserInterface $user)
	{

	}

	/**
	 * Returns an arrya of groups which the given
	 * user belongs to.
	 *
	 * @param  Cartalyst\Sentry\Users\UserInterface  $user
	 * @return array
	 */
	public function getGroups(UserInterface $user)
	{
		return $user->groups()->get();
	}

	/**
	 * Adds the user to the given group
	 *
	 * @param  Cartalyst\Sentry\Users\UserInterface  $user
	 * @param  Cartalyst\Sentry\Groups\GroupInterface  $group
	 * @return bool
	 */
	public function addGroup(UserInterface $user, GroupInterface $group)
	{
		if ($this->inGroup($user, $group))
		{
			return true;
		}

		$this->groups()->attach($group);
		return true;
	}

	/**
	 * Remove user from the given group.
	 *
	 * @param  Cartalyst\Sentry\Users\UserInterface  $user
	 * @param  Cartalyst\Sentry\Groups\GroupInterface  $group
	 * @return bool
	 */
	public function removeGroup(UserInterface $user, GroupInterface $group)
	{
		if ( ! $this->inGroup($user, $group))
		{
			return true;
		}

		$this->groups()->detatch($group);
		return true;
	}

	/**
	 * See if user is in the given group.
	 *
	 * @param  Cartalyst\Sentry\Users\UserInterface  $user
	 * @param  Cartalyst\Sentry\Groups\GroupInterface  $group
	 * @return bool
	 */
	public function inGroup(UserInterface $user, GroupInterface $group)
	{
		foreach ($user->getGroups() as $_group)
		{
			if ($_group->getGroupId() == $group->getGroupId())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns an array of merged permissions for each
	 * group the user is in.
	 *
	 * @param  Cartalyst\Sentry\Users\UserInterface  $user
	 * @return array
	 */
	public function getMergedPermissions(UserInterface $user)
	{
		$permissions = array();

		foreach ($this->getGroups($user) as $group)
		{
			$permissions = array_merge($permissions, $group->getGroupPermissions());
		}

		$permissions = array_merge($permissions, $user->getUserPermissions());

		return $permissions;
	}

	/**
	 * See if a user has a required permission. Permissions
	 * are merged from all groups the user belongs to
	 * and then are checked against the passed permission.
	 *
	 * @param  Cartalyst\Sentry\Users\UserInterface  $user
	 * @param  string  $permission
	 * @return bool
	 */
	public function hasAccess(UserInterface $user, $permission)
	{
		if ($user->isSuperUser())
		{
			return true;
		}

		$permissions = $this->getMergedPermissions($user);

		return (array_key_exists($permission, $permissions) and $permissions[$permission] == 1);
	}

	/**
	 * Create a new instance of the model.
	 *
	 * @return Illuminate\Database\Eloquent\Model
	 */
	public function createModel()
	{
		$class = '\\'.ltrim($this->model, '\\');

		return new $class();
	}

}