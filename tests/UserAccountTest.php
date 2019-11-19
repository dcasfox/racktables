<?php

class UserAccountTest extends RTTestCase
{
	const PSWDHASH = 'f7563fd105b011264532ef2d405082015bed948e';
	const REALNAME = 'Test User Account';
	private $user_name;
	private $user_id;

	public function setUp () : void
	{
		$this->user_name = $this->myString ('testuser');
		$this->user_id = commitCreateUserAccount ($this->user_name, self::REALNAME, self::PSWDHASH);
	}

	public function tearDown () : void
	{
		commitDeleteUserAccount ($this->user_id);
	}

	/**
	 * @group small
	 */
	public function testNormal ()
	{
		$this->assertArrayHasKey ($this->user_id, getAccountSearchResult ($this->user_name));
		$this->assertEquals ($this->user_id, getUserIDByUsername ($this->user_name));
		$this->assertNull (getUserIDByUsername ('x' . $this->user_name));
		$user = spotEntity ('user', $this->user_id);
		$this->assertEquals ($this->user_name, $user['user_name']);
		$this->assertEquals (self::PSWDHASH, $user['user_password_hash']);
		$this->assertEquals (self::REALNAME, $user['user_realname']);

		$this->user_name = 'x' . $this->user_name;
		commitUpdateUserAccount ($this->user_id, $this->user_name, 'x' . self::REALNAME, sha1 (self::PSWDHASH));

		$this->assertArrayHasKey ($this->user_id, getAccountSearchResult ($this->user_name));
		$this->assertEquals ($this->user_id, getUserIDByUsername ($this->user_name));
		$this->assertNull (getUserIDByUsername ('x' . $this->user_name));
		$user = spotEntity ('user', $this->user_id, TRUE);
		$this->assertEquals ($this->user_name, $user['user_name']);
		$this->assertEquals (sha1 (self::PSWDHASH), $user['user_password_hash']);
		$this->assertEquals ('x' . self::REALNAME, $user['user_realname']);
	}

	/**
	 * @group small
	 */
	public function testDuplicate ()
	{
		$this->expectException (RTDatabaseError::class);
		commitCreateUserAccount ($this->user_name, 'x' . self::REALNAME, sha1 (self::PSWDHASH));
	}

	/**
	 * @group small
	 */
	public function testInvalidPassword1 ()
	{
		$this->expectException (InvalidArgException::class);
		commitUpdateUserAccount ($this->user_id, $this->user_name, self::REALNAME, 'password');
	}

	/**
	 * @group small
	 */
	public function testInvalidPassword2 ()
	{
		$this->expectException (InvalidArgException::class);
		commitCreateUserAccount ($this->user_name, self::REALNAME, 'password');
	}

	/**
	 * @group small
	 */
	public function testDelete1 ()
	{
		$this->expectException (EntityNotFoundException::class);
		$user2_id = commitCreateUserAccount ($this->user_name . 'x', self::REALNAME, self::PSWDHASH);
		commitDeleteUserAccount ($user2_id);
		spotEntity ('user', $user2_id, TRUE);
	}

	/**
	 * @group small
	 */
	public function testDelete2 ()
	{
		$this->expectException (InvalidArgException::class);
		commitDeleteUserAccount (1);
	}

	/**
	 * @group small
	 */
	public function testDelete3 ()
	{
		$this->expectException (EntityNotFoundException::class);
		commitDeleteUserAccount (1000000);
	}
}
