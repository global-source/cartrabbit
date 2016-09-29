<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace MyPlugin\Models;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Contract for a Doctrine persistence layer ObjectRepository class to implement.
 *
 * @link   www.doctrine-project.org
 * @since  2.1
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Jonathan Wage <jonwage@gmail.com>
 */
class eventDispatcher implements EventDispatcherInterface
{

    public function dispatch($eventName, Event $event = null){

    }
    public function addListener($eventName, $listener, $priority = 0){

    }

    public function addSubscriber(EventSubscriberInterface $subscriber){

    }

    public function removeListener($eventName, $listener){

    }

    public function removeSubscriber(EventSubscriberInterface $subscriber){

    }

    public function getListeners($eventName = null){

    }

    public function hasListeners($eventName = null){

    }

    public function getListenerPriority($eventName, $listener)
    {
        // TODO: Implement getListenerPriority() method.
    }

}
