<?php


namespace Sesile\MainBundle\Manager;

use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Domain\Message;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class SesileMailer
 * @package Sesile\MainBundle\Manager
 */
class SesileMailer
{

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var string domain
     */
    protected $domain;
    /**
     * @var
     */
    protected $emailSenderAddress;
    /**
     * @var EngineInterface
     */
    private $template;

    /**
     * SesileMailer constructor.
     * @param \Swift_Mailer $mailer
     * @param EngineInterface $template
     * @param $domain
     * @param $sender
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Swift_Mailer $mailer,
        EngineInterface $template,
        $domain,
        $sender,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->template = $template;
        $this->domain = $domain;
        $this->emailSenderAddress = $sender;
    }

    /**
     * @param array $toAddresses
     * @param $subject
     * @param string|mixed $body the body can be the text, or the data for the template render
     * @param null $template the tempalte in the form of: @SesileMigration/mail/confirmationMigration.html.twig
     *
     * @return Message
     */
    public function send(array $toAddresses, $subject, $body, $template = null)
    {
        try {
            if ($template) {
                $body = $this->template->render($template, ['body' => $body]);
            }
            $message = (new \Swift_Message($subject))
                ->setFrom([$this->emailSenderAddress => $this->domain])
                ->setTo($toAddresses)
                ->setBody($body);
            $result = $this->mailer->send($message);

            return new Message(true, $result);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('[SesileMailer]/send error: %s', $e->getMessage())
            );

            return new Message(false, null, [$e->getMessage()]);
        }
    }
}