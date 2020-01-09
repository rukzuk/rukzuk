<?php
namespace Cms\Dao\Ticket;

use Cms\Dao\Ticket as Dao;
use Cms\Dao\Doctrine as DoctrineBase;
use Cms\Exception as CmsException;
use Exception;
use Seitenbau\Registry as Registry;
use Seitenbau\RandomGenerator as RandomGenerator;

/**
 * Doctrine
 *
 * @package      Cms
 * @subpackage   Dao
 */

class Doctrine extends DoctrineBase implements Dao
{
  /**
   * @param  string   $websiteId
   * @param  boolean  $isRedirect
   * @param  boolean  $isGet
   * @param  string   $requestConfig
   * @param  integer  $ticketLifetime
   * @param  integer  $remainingCalls
   * @param  integer  $sessionLifetime
   * @param  string   $credentials
   * @return string   ID des Tickets
   */
  public function create(
      $websiteId,
      $isRedirect,
      $isGet,
      $requestConfig,
      $ticketLifetime,
      $remainingCalls,
      $sessionLifetime,
      $credentials
  ) {
    
    // Neues (nicht vorhandenes) Ticket erstellen
    $newTicketId = null;
    $secCounter = 0;
    $ticketExists = true;
    do {
      $newTicketId = RandomGenerator::generateString(Dao::TICKET_LENGTH, Dao::TICKET_CHARS);
      if (strlen($newTicketId) != Dao::TICKET_LENGTH) {
        echo '<b>Falsche Ticketlänge: '.$newTicketId."<br />";
        exit;
      }
      $ticketExists = $this->existsTicket($newTicketId);
    } while ($ticketExists && ($secCounter++)<1000);
    if ($ticketExists) {
      throw new CmsException(1302, __METHOD__, __LINE__);
    }
    
    try {
      $ticket = new \Orm\Entity\Ticket();
      $ticket->setId($newTicketId);
      $ticket->setTimestamp(time());
      $ticket->setWebsiteid($websiteId);
      $ticket->setIsredirect($isRedirect);
      $ticket->setIsget($isGet);
      $ticket->setRequestconfig(\Seitenbau\Json::encode($requestConfig));
      $ticket->setTicketLifetime($ticketLifetime);
      $ticket->setRemainingCalls($remainingCalls);
      $ticket->setSessionLifetime($sessionLifetime);
      $ticket->setCredentials($credentials);

      $this->getEntityManager()->persist($ticket);
      $this->getEntityManager()->flush();
    } catch (Exception $e) {
      throw new CmsException(1301, __METHOD__, __LINE__, null, $e);
    }

    return $ticket;
  }

  /**
   * @param  string      $ticketId
   * @return array
   */
  public function getById($ticketId)
  {
    try {
      $ticket = $this->getEntityManager()
                  ->getRepository('Orm\Entity\Ticket')
                  ->findOneById($ticketId);
      return $ticket;
    } catch (Exception $e) {
      throw new CmsException(1303, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @param  string      $ticketId
   * @return boolean
   */
  public function existsTicket($ticketId)
  {
    try {
      $ticket = $this->getEntityManager()
                  ->getRepository('Orm\Entity\Ticket')
                  ->findOneById($ticketId);
      return $ticket !== null;
    } catch (Exception $e) {
      throw new CmsException(1304, __METHOD__, __LINE__, null, $e);
    }
  }

  /**
   * @param  string      $ticketId
   */
  public function decreaseCalls($ticketId)
  {
    try {
      $dql = 'UPDATE Orm\Entity\Ticket t SET t.remainingcalls = t.remainingcalls - 1 WHERE t.id = :ticketid';
      $query = $this->getEntityManager()->createQuery($dql);
      $query->setParameter('ticketid', $ticketId);
      $query->execute();
    } catch (Exception $e) {
      throw new CmsException(1005, __METHOD__, __LINE__, null, $e);
    }
  }

  public function deleteInvalidTickets()
  {
    try {
      $dql = 'DELETE FROM Orm\Entity\Ticket t WHERE t.remainingcalls <= 0 OR :curtime > (t.timestamp + t.ticketlifetime)';
      $query = $this->getEntityManager()->createQuery($dql);
      $query->setParameter('curtime', time());
      $query->execute();
    } catch (Exception $e) {
      throw new CmsException(1006, __METHOD__, __LINE__, null, $e);
    }
  }
}
