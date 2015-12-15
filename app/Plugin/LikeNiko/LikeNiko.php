<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\LikeNiko;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class LikeNiko
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

     public function onKernelController($event)
    {
        $controller = $event->getController();
    }

	public function setNikoMassegeBox(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        //if ('GET' === $request->getMethod()) {
            // RedirectResponseかどうかで判定する.
            //if (!$response instanceof RedirectResponse) {
            //    return;
            //}

            $crawler = new Crawler($response->getContent());
            $html = $this->getHtml($crawler);
            $form = $this->app['form.factory']->createBuilder('likeniko')->getForm();
            $form->handleRequest($request);
            $parts = $this->app->renderView('LikeNiko/View/default/nikobox.twig', array(
                'form' => $form->createView()
            ));
            try {
                $oldHtml = $crawler->filter('.front_page')->first()->html();
                //$crawler->addHtmlContent($parts);
                $newHtml = $oldHtml . $parts;
                $html = str_replace($oldHtml, $newHtml, $html);
            } catch (\InvalidArgumentException $e) {
            }
            //var_dump($html);
            $response->setContent(html_entity_decode($html, ENT_NOQUOTES, 'UTF-8'));
        }

    /**
     * 解析用HTMLを取得
     *
     * @param Crawler $crawler
     * @return string
     */
    private function getHtml(Crawler $crawler)
    {
        $html = '';
        foreach ($crawler as $domElement) {
            $domElement->ownerDocument->formatOutput = true;
            $html .= $domElement->ownerDocument->saveHTML();
        }
        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }
}
