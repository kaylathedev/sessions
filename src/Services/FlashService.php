<?php
namespace WaffleSystems\Session\Services;

use \WaffleSystems\Session\SessionInterface;

class FlashService
{

    private $session;
    private $pathPrefix;
    private $delimiter = " \n";
    private $onFormatCallable;

    /**
     * Please use the constructor instead.
     * 
     * @deprecated
     * @return FlashService
     */
    public static function createFromSession(SessionInterface $session)
    {
        return new FlashService($session);
    }

    /**
     * @param SessionInterface $session
     * 
     * @return void
     */
    public function __construct(SessionInterface $session)
    {
        $this->session          = $session;
        $this->onFormatCallable = function ($message) {
            $type = $message['type'];
            if ('error' === $type) {
                $type = 'danger';
            }
            if (0 === strlen($type)) {
                $type = 'info';
            }
            $text = $message['text'];
            echo '<div class="alert alert-', $type, '">';
            echo '<button type="button" class="close" data-dismiss="alert">';
            echo '<span aria-hidden="true">&times;</span>';
            echo '<span class="sr-only">Close</span>';
            echo '</button>';
            echo $text;
            echo '</div>';
        };
    }

    /**
     * @param string $delimiter
     * 
     * @return void
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @return void
     */
    public function setOnFormat($callable)
    {
        $this->onFormatCallable = $callable;
    }

    /**
     * @return boolean
     */
    public function hasAny($type = null)
    {
        $messages = $this->session->get($this->pathPrefix . 'messages');
        if (null === $type) {
            return count($messages) > 0;
        }
        foreach ($messages as $message) {
            if ($type === $message['type']) {
                return true;
            }
        }
        return false;
    }

    public function error($text, $timeout = 3600)
    {
        $this->add($text, 'error', $timeout);
    }

    public function warning($text, $timeout = 3600)
    {
        $this->add($text, 'warning', $timeout);
    }

    public function info($text, $timeout = 3600)
    {
        $this->add($text, 'info', $timeout);
    }

    public function success($text, $timeout = 3600)
    {
        $this->add($text, 'success', $timeout);
    }

    /**
     * @return void
     */
    public function add($text, $type = null, $timeout = 3600)
    {
        $path     = $this->pathPrefix . 'messages';
        $messages = $this->session->get($path);

        if ($messages === null) {
            $messages = [];
        }
        $messages[] = ['type' => $type, 'timeout' => $timeout, 'text' => $text];

        $this->session->set($path, $messages);
    }

    /**
     * @return void
     */
    public function addMany(array $messages, $type = null, $timeout = 3600)
    {
        foreach ($messages as $message) {
            $this->add($message, $type, $timeout);
        }
    }

    /**
     * @return void
     */
    public function displayAll()
    {
        $this->filterMessageOutput(function() {
            return true;
        });
    }

    /**
     * @return void
     */
    public function display($type = null)
    {
        $this->filterMessageOutput(function($message) use($type) {
            return $type === $message['type']; 
        });
    }

    /**
     * @param callable $filterCallback
     */
    private function filterMessageOutput($filterCallback)
    {
        $path     = $this->pathPrefix . 'messages';
        $messages = $this->session->get($path);

        if (!is_array($messages)) {
            return;
        }

        $output = [];
        foreach ($messages as $key => $message) {
            if (call_user_func($filterCallback, $message)) {
                /* TODO: Document the onFormatCallable callback. */
                $formattedMessage = call_user_func($this->onFormatCallable, $message);
                if (false === $formattedMessage) {
                    continue;
                }
                $output[] = $formattedMessage;
                unset($messages[$key]);
            }
        }
        $this->session->set($path, $messages);
        echo implode($this->delimiter, $output);
    }
}
