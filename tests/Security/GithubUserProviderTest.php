<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\GithubUserProvider;
use GuzzleHttp\Client;
use JMS\Serializer\Serializer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class GithubUserProviderTest extends TestCase
{
    private MockObject | Client | null $client;
    private MockObject | Serializer | null $serializer;
    private MockObject | ResponseInterface | null $response;

    public function setUp(): void
    {
        $this->client = $this->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder('JMS\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function tearDown(): void
    {
        $this->client = null;
        $this->serializer = null;
        $this->response = null;
    }
    
    public function testLoadUserByUsernameReturningAUser(): void
    {
        $userData = [
            'login' => 'a login',
            'name' => 'user name',
            'email' => 'adress@mail.com',
            'avatar_url' => 'url to the avatar',
            'html_url' => 'url to profile'
        ];

        $this->client->expects($this->once())->method('get')->willReturn($this->response);
        $this->serializer->expects($this->once())->method('deserialize')->willReturn($userData);

        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);
        $user = $githubUserProvider->loadUserByUsername('an-access-token');
        $expectedUser = new User($userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']);

        $this->assertEquals($expectedUser, $user);
    }

    public function testLoadUserByUsernameThrowingLogicException(): void
    {
        $userData = [];

        $this->client->expects($this->once())->method('get')->willReturn($this->response);
        $this->serializer->expects($this->once())->method('deserialize')->willReturn($userData);

        $this->expectException(\LogicException::class);

        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);
        $githubUserProvider->loadUserByUsername('an-access-token');
    }
}
