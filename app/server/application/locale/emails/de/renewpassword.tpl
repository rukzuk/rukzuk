<?php
// set subject
$this->subject = 'Neues Passwort für dein Login auf der Webdesign-Plattform';
?>
Hallo <?php echo $this->escape($this->optinUser->getFirstname()); ?>,

bitte besuche folgenden Link um ein neues Passwort für die Webdesign-Plattform festzulegen:
<?php echo $this->escape($this->optinUrl).'?t='.$this->escape($this->optin->getCode()).'&u='.$this->escape($this->optinUser->getEmail())."\n"; ?>

Plattform-Adresse: <?php echo $this->escape($this->spaceUrl)."\n"; ?>
E-Mail: <?php echo $this->escape($this->optinUser->getEmail())."\n"; ?>




/////////////////////////////////
Diese E-Mail wurde im Auftrag von
<?php echo $this->escape($this->fromName)."\n"; ?>
<?php echo $this->escape($this->fromEmail)."\n"; ?>
gesendet.