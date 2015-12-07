Mitteilung
----------------------
<?php echo $this->feedback->getUserFeedback(); ?>


Debug-Infos
----------------------
<?php echo $this->feedback->getWebhost(); ?>


<?php echo $this->feedback->getPlatform(); ?>


<?php echo $this->feedback->getUserAgent(); ?>


<?php if (is_array($this->feedback->getClientErrors()) 
          && count($this->feedback->getClientErrors()) > 0
      ){
        $showErrorSeperator = false;
        foreach ($this->feedback->getClientErrors() as $clientError)
        {
          if ($showErrorSeperator == true)
          {
            echo "\n\n" . '***** Next Error *****' . "\n\n";
          }
          
          if (isset($clientError->date))
          {
            echo date('d.m.Y H:i:s', strtotime($clientError->date)) . "\n";
          }
          if (isset($clientError->text))
          {
            echo $clientError->text;
          }
          $showErrorSeperator = true;
        }
      }
?>