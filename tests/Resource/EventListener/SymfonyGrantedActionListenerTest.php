<?php

/*
 * This file is part of the FiveLab ResourceBundle package
 *
 * (c) FiveLab
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FiveLab\Bundle\ResourceBundle\Tests\Resource\EventListener;

use FiveLab\Bundle\ResourceBundle\Resource\Action\SymfonyGrantedAction;
use FiveLab\Bundle\ResourceBundle\Resource\EventListener\SymfonyGrantedActionListener;
use FiveLab\Component\Resource\Resource\Action\ActionCollection;
use FiveLab\Component\Resource\Resource\Action\ActionInterface;
use FiveLab\Component\Resource\Resource\ActionedResourceInterface;
use FiveLab\Component\Resource\Resource\ResourceInterface;
use FiveLab\Component\Resource\Serializer\Events\BeforeNormalizationEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class SymfonyGrantedActionListenerTest extends TestCase
{
    /**
     * @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationChecker;

    /**
     * @var SymfonyGrantedActionListener
     */
    private $listener;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->listener = new SymfonyGrantedActionListener($this->authorizationChecker);
    }

    /**
     * @test
     */
    public function shouldNotProcessIfPassActionedResource(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->listener->onBeforeNormalization(new BeforeNormalizationEvent($resource, 'json', []));
    }

    /**
     * @test
     */
    public function shouldSuccessProcess(): void
    {
        $resource = $this->createMock(ActionedResourceInterface::class);
        $notGrantedAction = $this->createMock(ActionInterface::class);
        $action1 = $this->createMock(ActionInterface::class);
        $grantAction1 = new SymfonyGrantedAction($action1, 'SOME1', (object) ['some' => 'value']);

        $action2 = $this->createMock(ActionInterface::class);
        $grantAction2 = new SymfonyGrantedAction($action2, 'SOME2', (object) ['some' => 'value']);

        $resource->expects(self::once())
            ->method('getActions')
            ->willReturn(new ActionCollection($notGrantedAction, $grantAction1, $grantAction2));

        $this->authorizationChecker->expects(self::exactly(2))
            ->method('isGranted')
            ->with(self::logicalOr('SOME1', 'SOME2'), (object) ['some' => 'value'])
            ->willReturnCallback(function ($attribute) {
                if ('SOME1' === $attribute) {
                    return false;
                }

                return true;
            });

        $resource->expects(self::once())
            ->method('removeAction')
            ->with($grantAction1);

        $this->listener->onBeforeNormalization(new BeforeNormalizationEvent($resource, 'json', []));
    }
}
