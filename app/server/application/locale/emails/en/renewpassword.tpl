<?php
// set subject
$this->subject = 'New password for your Web Design Platform login';
?>
Hello <?php echo $this->escape($this->optinUser->getFirstname()); ?>,

please visit the following link to set a new password for the Web Design Platform:
<?php echo $this->escape($this->optinUrl).'?t='.$this->escape($this->optin->getCode()).'&u='.$this->escape($this->optinUser->getEmail())."\n"; ?>

Platform address: <?php echo $this->escape($this->spaceUrl)."\n"; ?>
Email: <?php echo $this->escape($this->optinUser->getEmail())."\n"; ?>




/////////////////////////////////
This email was sent on behalf of
<?php echo $this->escape($this->fromName)."\n"; ?>
<?php echo $this->escape($this->fromEmail); ?>