<?php


namespace Sesile\MainBundle\Tests\Manager;


use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Manager\SesileMailer;
use Sesile\MainBundle\Tests\Tools\SesileWebTestCase;

class SesileMailerTest extends SesileWebTestCase
{
    /**
     * @var SesileMailer
     */
    protected $sesileMailer;
    protected $templating;
    protected $domain;
    protected $emailSenderAddress;
    protected $logger;

    public function setUp()
    {
        $this->sesileMailer = $this->getContainer()->get('sesile.mailer');
        $this->templating = $this->getContainer()->get('templating');
        $this->domain = $this->getContainer()->getParameter('domain');
        $this->emailSenderAddress = $this->getContainer()->getParameter('email_sender_address');
        $this->logger = $this->createMock(LoggerInterface::class);
        parent::setUp();
    }

    public function testSendEmail()
    {
        $swiftMailer = $this->getMockBuilder(\Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->setMethods(['send'])
            ->getMock();

        $swiftMailer->expects(self::once())
            ->method('send')
            ->willReturn(1);
        $sesileMailer = new SesileMailer($swiftMailer, $this->templating, $this->domain, $this->emailSenderAddress, $this->logger);
        $mailTo = ['sictiam@sictiam.fr'];
        $subject = 'subject';
        $body = 'The Body';
        $result = $sesileMailer->send($mailTo, $subject, $body);
        self::assertInstanceOf(Message::class, $result);
        self::assertTrue($result->isSuccess());
    }

    public function testSendEmailShouldReturnFalseIfExceptionIsThrown()
    {
        $swiftMailer = $this->getMockBuilder(\Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->setMethods(['send'])
            ->getMock();

        $swiftMailer->expects(self::once())
            ->method('send')
            ->willThrowException(new \Exception('errooooor'));
        $sesileMailer = new SesileMailer($swiftMailer, $this->templating, $this->domain, $this->emailSenderAddress, $this->logger);
        $mailTo = ['sictiam@sictiam.fr'];
        $subject = 'subject';
        $body = 'The Body';
        $result = $sesileMailer->send($mailTo, $subject, $body);
        self::assertInstanceOf(Message::class, $result);
        self::assertFalse($result->isSuccess());
    }
}