# Booosta email module - Tutorial

## Abstract

This tutorial covers the email module of the Booosta PHP framework. If you are new to this framework, we strongly
recommend, that you first read the [general tutorial of Booosta](https://github.com/buzanits/booosta-installer/blob/master/tutorial/tutorial.md).

## Purpose

The purpose of this module is to send emails from your scripts.

## Installation

If this module is not installed, it can be loaded with

```
composer require booosta/email
```

This also loads addtional dependent modules.

## Requirements

To be able to send mails with this module, you have to have a PHP installation, that can send mails or have access to a SMTP server.

## Usage

```
$mailer = $this->makeInstance('email', $sender, $receivers, $subject, $content);

# $sender is the sender (From:) of this mail. It can be just an email address (me@here.com)
# or have an optional name (This is me<me@here.com>)
#
# $receivers is a string with the destination address (you@there.com) or an array of addresses
#
# $subject is a string with the subject of the email message
#
# $content is a string with the content of the email

$mailer = $this->makeInstance('email', 'me@here.com', ['you@there.com', 'they@there.com'], 'Hello', $my_content);
```
There are various methods to set different data on the email:
```
# set the sender after instantiation
$mailer->set_sender('us@here.com');

# set the subject after instantiation
$mailer->set_subject('Hello, World!');

# set the content after instantiation
# per default the content is HTML code
$mailer->set_content('Hello, Arthur.<br>The answer to your question is 42.');

# add additional content
$mailer->add_content(' Don't panic!');

# set the mail headers, which have to be in an array
$mailer->set_header(['Reply-To' => 'him@here.com', 'X-Secretcode' => '123456']);

# add an additional header to the existing
$mailer->add_header('Reply-To', 'him@here.comm');

# set receivers after instantiation
$mailer->set_receivers('you@there.com');
$mailer->set_receivers(['you@there.com', 'they@there.com']);

# add a receiver
$mailer->add_receivers('they@there.com');
$mailer->add_receivers(['they@there.com', 'nobody@there.com']);

# set address(es) for carbon copies (CC:)
$mailer->set_cc('you@there.com');
$mailer->set_cc(['you@there.com', 'they@there.com']);

# set address(es) for blind carbon copies (BCC:)
$mailer->set_bcc('you@there.com');
$mailer->set_bcc(['you@there.com', 'they@there.com']);

# set the return path, which is usually the sender address, but can be changed
$mailer->set_returnpath('ours@here.com');

# set the mail backend to use an SMTP server
$mailer->set_backend('smtp');

# set smtp parameters
$mailer->set_smtp_params(['host' => 'smtp.here.com', 'auth' => true, 'username' => 'user1234', 'password' => 'very$ecret', 'port' => 465]);

# disable peer verification - do not check the certificate of the peer host
$mailer->verify_peer(false);

# set debug level - it is the debug level of the uses library phpmailer. See the (documentation)[https://phpmailer.github.io/PHPMailer/classes/PHPMailer-PHPMailer-SMTP.html] for more info.
$mailer->set_debuglevel(2);

# set the inline images. Include the image in the html code of the content with: <img width="200" height="150" src="cid:mycoolimage">
$mailer->set_images(['mycoolimage' => 'images/cool.png']);

# add additional inline images
$mailer->add_images('images/cool1.png', 'mycoolimage1');
$mailer->add_images(['mycoolimage2' => 'images/cool2.png', 'mycoolimage3' => 'images/cool3.png']);

# put attachments to the email
$mailer->set_attachments(['mycooldoc1.pdf' => 'pdfs/cool1.pdf', 'mycooltext.docx' => 'work/doc/cool1.docx']);
$mailer->add_attachment('work/xls/cool1.xlsx', 'mycoolsheet.xlsx');
$mailer->add_attachments(['coolpic.jpg' => 'pics/cool.jpg', 'test.zip' => 'work/test.zip']);
$mailer->add_attachment_data('info.txt', 'This is the content of the info.txt');

# set the content type. Default is 'html'. It can also be 'text' or 'plain'.
$mailer->set_contentype('text');
```
Finally send the email. `$result` holds `true` in case of success. An error message otherwise.
```
$result = $mailer->send();
```
