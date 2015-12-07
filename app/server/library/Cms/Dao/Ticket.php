<?php
namespace Cms\Dao;

/**
 * Schnittstelle fuer Ticket Datenabfragen
 *
 * @package      Cms
 * @subpackage   Dao
 */

interface Ticket
{
  const TICKET_CHARS = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-";
  const TICKET_LENGTH = 10;

  /**
   * Erstellt ein neues Ticket und gibt die neue TicketId zurueck
   *
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
  public function create($websiteId, $isRedirect, $isGet, $requestConfig, $ticketLifetime, $remainingCalls, $sessionLifetime, $credentials);

  /**
   * Gibt ein Ticket der ID zurueck
   *
   * @param  string  $ticketId
   * @return array
   */
  public function getById($ticketId);

  /**
   * Ueberprueft ob ein ticket existiert
   *
   * @param  string  $ticketId
   * @return boolean
   */
  public function existsTicket($ticketId);

  /**
   * Reduziert den internen Aufrufzaehler um eins
   *
   * @param  string  $ticketId
   */
  public function decreaseCalls($ticketId);

  /**
   * Entfernt die nicht mehr gueltigen Tickets
   */
  public function deleteInvalidTickets();
}
