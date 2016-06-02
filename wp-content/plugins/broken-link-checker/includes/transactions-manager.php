<?php

class TransactionManager
{
    private $isTransactionStarted = false;
    private static $instance;

    public function start()
    {
        global $wpdb;

        if (!$this->isTransactionStarted) {
            $wpdb->query('BEGIN');
            $this->isTransactionStarted = true;
        }
    }

    public function commit()
    {
        global $wpdb;

        $this->start();

        try {
            $wpdb->query('COMMIT');
            $this->isTransactionStarted = false;
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            $this->isTransactionStarted = false;
        }
    }

    static public function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new TransactionManager();
        }

        return self::$instance;
    }
}
