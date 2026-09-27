<?php
/**
*
* Admin Activate Email Extras extension for phpBB.
*
*/

namespace burnsideconsulting\adminactivateemail\notification\type;

/**
* Extends phpBB's own admin_activate_user notification type rather than
* replacing it. All of the core logic (who gets notified, item ids, the
* notification title in the UCP, etc.) is inherited unchanged. We only
* override what we need: the extra email template variables, and which
* email template file gets used.
*/
class admin_activate_user extends \phpbb\notification\type\admin_activate_user
{
	/** @var \phpbb\profilefields\manager */
	protected $profile_manager;

	/**
	* Injected via config/services.yml calls:
	*
	* @param \phpbb\profilefields\manager $profile_manager
	*/
	public function set_profile_manager(\phpbb\profilefields\manager $profile_manager)
	{
		$this->profile_manager = $profile_manager;
	}

	/**
	* {@inheritdoc}
	*
	* Point at our own copy of the template (see language/en/email/admin_activate.txt
	* in this extension) instead of core's language/en/email/admin_activate.txt.
	* The '@vendor_extension/template_name' syntax tells the messenger to look
	* inside this extension's language folder.
	*/
	public function get_email_template()
	{
		return '@burnsideconsulting_adminactivateemail/admin_activate';
	}

	/**
	* {@inheritdoc}
	*
	* Start from whatever core would have sent, then add the two extra
	* variables. If core changes/add variables in a future version, they
	* still flow through here automatically.
	*/
	public function get_email_template_variables()
	{
		$vars = parent::get_email_template_variables();

		$vars['U_EMAIL'] = $this->get_user_email();
		$vars['U_VERSION'] = $this->get_user_version_field();

		return $vars;
	}

	/**
	* Look up the registrant's email address via the (already injected) user_loader.
	*
	* @return string
	*/
	protected function get_user_email()
	{
		$this->user_loader->load_users(array($this->item_id));
		$user_row = $this->user_loader->get_user($this->item_id);

		return isset($user_row['user_email']) ? $user_row['user_email'] : '';
	}

	/**
	* Look up the 'proclaim_version' custom profile field for the registrant.
	*
	* @return string
	*/
	protected function get_user_version_field()
	{
		$profile_data = $this->profile_manager->grab_profile_fields_data($this->item_id);

		return isset($profile_data[$this->item_id]['proclaim_version']['value'])
			? $profile_data[$this->item_id]['proclaim_version']['value']
			: '';
	}
}
