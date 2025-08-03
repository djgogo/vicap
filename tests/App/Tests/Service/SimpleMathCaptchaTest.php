<?php

namespace App\Tests\Service;

use App\Service\SimpleMathCaptcha;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SimpleMathCaptchaTest extends TestCase
{
    private $requestStack;
    private $session;
    private $captcha;

    protected function setUp(): void
    {
        // Create mocks for the dependencies
        $this->session = $this->createMock(SessionInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        
        // Configure the request stack mock to return the session mock
        $this->requestStack->method('getSession')
            ->willReturn($this->session);
        
        // Create the captcha service with the mocked dependencies
        $this->captcha = new SimpleMathCaptcha($this->requestStack);
    }

    public function testGenerateCaptcha(): void
    {
        // The session should be called with the 'captcha_result' key and some integer value
        $this->session->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo('captcha_result'),
                $this->isType('integer')
            );
        
        // Call the method
        $question = $this->captcha->generateCaptcha();
        
        // Assert that the question is a string and contains the expected format
        $this->assertIsString($question);
        $this->assertStringContainsString('Recpatcha:', $question);
        $this->assertStringContainsString('+', $question);
        $this->assertStringContainsString('?', $question);
    }

    public function testIsValidWithCorrectAnswer(): void
    {
        // Configure the session to return 5 as the expected result
        $this->session->method('get')
            ->with('captcha_result')
            ->willReturn(5);
        
        // The session should be called to remove the 'captcha_result' key
        $this->session->expects($this->once())
            ->method('remove')
            ->with('captcha_result');
        
        // Test with the correct answer
        $result = $this->captcha->isValid(5);
        
        // Assert that the result is true
        $this->assertTrue($result);
    }

    public function testIsValidWithIncorrectAnswer(): void
    {
        // Configure the session to return 5 as the expected result
        $this->session->method('get')
            ->with('captcha_result')
            ->willReturn(5);
        
        // The session should not be called to remove the 'captcha_result' key
        $this->session->expects($this->never())
            ->method('remove');
        
        // Test with an incorrect answer
        $result = $this->captcha->isValid(6);
        
        // Assert that the result is false
        $this->assertFalse($result);
    }
}