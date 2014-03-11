MailChimp
=========
It is specific use for CakePHP plugin.

Installation
============
Can install it via Composer or git submodule.
Composer
--------
    composer install
  
or if you are already using composer
  
    composer update 

Git Submodule
-------------

    git submodule add https://github.com/prabpornpra/MailChimp.git pathForYourPluginFolder/nameThePlugin
  
Configuration
=============
New for CakePHP 2.0, plugins need to be loaded manually in app/Config/bootstrap.php.

    CakePlugin::load('MailChimp')
  
In app/Config, create a php file and name it ```mail_chimp.php```. Inside mail_chimp.php add these code below.

    $config['Chimp']['url'] = 'api.mailchimp.com/';
    $config['Chimp']['key'] = "Your API Key from your MailChimp account"
    $config['Chimp']['version'] = '2.0';
    $config['Chimp']['lists_subscribe'] = '/lists/subscribe';
    $config['Chimp']['lists_member_info'] = '/lists/member-info';
  
Usage
=====
Version 1.0* has two methods

Subscribe email
---------------

Add an email into subscribe list

    listSubscribe($listId, $params)

$listId: The list id to connect to, you can get it from your MailChimp account.

$params: email for subscribe
Ex:
  
    $params = array (
      'email' => array('email' => 'your email')
    );

You can add more option into $params, see the format in, http://apidocs.mailchimp.com/api/2.0/lists/subscribe.php
If the method is called successfully, an confirmation email will be sent to the email that want to get subscribe.

Check email status in subscribe list
------------------------------------

    getEmailStatus($listId, $email) 

$listId: The list id to connect to, you can get it from your MailChimp account.

$email: The email that you want to check the status
  
This method will return a string status.
  
    Pending: The email confirmation is sent to this email, but the user haven't response yet.
    Subscribe: This email is already subscribe in the list.
    Unsubscribe: This email is unsubscribe in the list.
    Not subscribe: This email haven't been subscribe in the list before.
    
License
=======
MIT License
See LICENSE file.