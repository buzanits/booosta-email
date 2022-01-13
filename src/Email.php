<?php
namespace booosta\email;

use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception;

use \booosta\Framework as b;
b::init_module('email');

class Email extends \booosta\base\Module
{ 
  use moduletrait_email;

  protected $sender, $receivers, $subject;
  protected $cc, $bcc;
  protected $header;
  protected $returnpath;
  protected $images, $attachments, $attachment_data;
  protected $content;
  protected $contenttype;
  protected $backend, $smtp_params;
  protected $debuglevel;

  public function __construct($sender = null, $receivers = null, $subject = null, $content = '')
  {
    parent::__construct();

    $this->sender = $sender;

    if($receivers == null) $this->receivers = [];
    elseif(is_array($receivers)) $this->receivers = $receivers;
    else $this->receivers = [$receivers];

    $this->header = [];
    $this->images = [];
    $this->attachments = [];
    $this->attachment_data = [];

    $this->subject = $subject;
    $this->content = $content;
    $this->contenttype = 'text/html';

    $this->backend = 'mail';
  }

  public function set_sender($sender) { $this->sender = $sender; }
  public function set_subject($subject) { $this->subject = $subject; }
  public function set_content($content) { $this->content = $content; }
  public function add_content($content) { $this->content .= $content; }
  public function set_header($header) { $this->header = $header; }
  public function add_header($name, $content) { $this->header[$name] = $content; }
  public function set_receivers($receivers) { $this->receivers = $receivers; }
  public function set_cc($cc) { $this->cc = $cc; }
  public function set_bcc($bcc) { $this->bcc = $bcc; }
  public function set_backend($backend) { $this->backend = $backend; }
  public function set_debuglevel($debuglevel) { $this->debuglevel = $debuglevel; }
  public function set_smtp_params($smtp_params) { $this->smtp_params = $smtp_params; }
  public function set_returnpath($returnpath) { $this->returnpath = $returnpath; }

  public function add_receivers($receivers)
  {
    if(is_array($receivers)) $this->receivers = array_merge($this->receivers, $receivers);
    else $this->receivers[] = $receivers;
  }

  public function set_images($images) { $this->images = $images; }

  public function add_images($images, $name = null) 
  {
    if(is_array($images)) $this->images = array_merge($this->images, $images);
    else $this->images[$name] = $images;
  }

  public function set_attachments($attachments) { $this->attachments = $attachments; }
  public function add_attachment($attachment, $name = null) { $this->attachments[$name] = $attachment; }

  public function add_attachments($attachments) 
  {
    if(is_array($attachments)) $this->attachments = array_merge($this->attachments, $attachments);
    else $this->attachments[] = $attachments;
  }

  public function add_attachment_data($name, $data) { $this->attachment_data[$name] = $data; }

  public function set_contenttype($type)
  {
    if($type == 'html') $type = 'text/html';
    if($type == 'text') $type = 'text/plain';
    if($type == 'plain') $type = 'text/plain';

    $this->contenttype = $type;
  }

  public function send()
  {
    if(!is_array($this->receivers)) return false;
  
    if(strstr($this->sender, '<')):
      list($sender_name, $senderaddr) = explode('<', $this->sender);
      $senderaddr = str_replace('>', '', $senderaddr);
    else:
      $sender_name = '';
      $senderaddr = $this->sender;
    endif;
    #\booosta\debug("$sender_name, $senderaddr");
 
    if($this->contenttype == 'text/html'):
      $htmlcode = $this->html_umlaut($this->content);
      $tmpcontent = str_replace('<br>', "\n", $this->content);
      $txtcode = html_entity_decode(strip_tags($tmpcontent), ENT_NOQUOTES, 'UTF-8');
    else:   //  text/plain
      $txtcode = $this->content;
    endif;
  
    $mailer = new PHPMailer();
    $mailer->CharSet = 'UTF-8';

    if($this->debuglevel !== null):
      $mailer->SMTPDebug = $this->debuglevel;
      $mailer->DebugOutput = function($str, $level) { \booosta\debug($str); };
    endif;

    $mailer->setFrom($senderaddr, $sender_name);
    $mailer->Subject = $this->subject;

    if($this->cc):
      if(!is_array($this->cc)) $this->cc = [$this->cc];
      foreach($this->cc as $name=>$cc):
        if(is_numeric($name)) $name = '';
        $mailer->addCC($cc, $name);
      endforeach;
    endif;

    if($this->bcc):
      if(!is_array($this->bcc)) $this->bcc = [$this->bcc];
      foreach($this->bcc as $name=>$bcc):
        if(is_numeric($name)) $name = '';
        $mailer->addBCC($bcc, $name);
      endforeach;
    endif;

    if($this->contenttype == 'text/html'):
      $mailer->msgHTML($htmlcode , __DIR__);
      $mailer->AltBody = $txtcode;
    else:
      $mailer->Body = $txtcode;
    endif;
  
    if(is_array($this->images))
      foreach($this->images as $name=>$image)
        $mailer->addEmbeddedImage($image, $name);

    if(is_array($this->attachments))
      foreach($this->attachments as $name=>$attachment):
        if(is_numeric($name)) $name = '';
        $mailer->addAttachment($attachment, $name);
      endforeach;

    if(is_array($this->attachment_data))
      foreach($this->attachment_data as $name=>$data)
        $mailer->addStringAttachment($data, $name);

    if(is_array($this->header))
      foreach($this->header as $name=>$data)
        $mailer->addCustomHeader($name, $data);

    if($this->backend == 'smtp'):
      $mailer->isSMTP();
      $mailer->AuthType = 'LOGIN';
      $mailer->Host = $this->smtp_params['host'] ?? 'localhost';
      $mailer->SMTPAuth = $this->smtp_params['auth'] ?? false;
      $mailer->Username = $this->smtp_params['username'];
      $mailer->Password = $this->smtp_params['password'];
      $mailer->Port = $this->smtp_params['port'] ?? 25;
    endif;

    foreach($this->receivers as $rec_name=>$receiver):
      if(is_numeric($rec_name)) $rec_name = '';
      $mailer->addAddress($receiver, $rec_name);
    endforeach;
  
    if($this->returnpath) $mailer->Sender = $this->returnpath;

    $result = $mailer->send();
    if($result !== true) return 'ERROR: ' . $mailer->ErrorInfo;
    return true;
  } 


  protected function html_umlaut($str)
  {
    $str = str_replace("Ã<84>", "&Auml;", $str);
    $str = str_replace("Ã<96>", "&Ouml;", $str);
    $str = str_replace("Ã<9c>", "&Uuml;", $str);
    $str = str_replace("Ã¤", "&auml;", $str);
    $str = str_replace("Ã¶", "&ouml;", $str);
    $str = str_replace("Ã¼", "&uuml;", $str);
    $str = str_replace("Ã<9f>", "&szlig;", $str);
  
    return $str;
  }
}
