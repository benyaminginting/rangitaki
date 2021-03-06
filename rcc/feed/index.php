<?php
/**
 * PHP Version 7
 *
 * @category Atom_Feed
 * @package  Rcc
 * @author   Marcel Kapfer (mmk2410) <marcelmichaelkapfer@yahoo.co.nz>
 * @license  MIT License
 * @link     https://gitlab.com/mmk2410/rangitaki
 *
 * Feed Generator
 *
 * The MIT License
 *
 * Copyright 2015 - 2016 (c) mmk2410.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

date_default_timezone_set('UTC');

require "../../vendor/autoload.php";
require "../../res/php/Config.php";
require_once "../../res/php/ArticleGenerator.php";
use PicoFeed\Syndication\AtomFeedBuilder;
use PicoFeed\Syndication\AtomItemBuilder;
use \mmk2410\rbe\config\Config as Config;

$config = new Config('../../config.yaml', '../../vendor/autoload.php');
$settings = $config->getConfig();

include '../ssl.php';

session_start();

if ($_SESSION['login']) {
    $art_dir = "./../../articles/" . $_GET['blog'] . "/";
    $feed_path = "./../../feed/" . $_GET['blog'] . ".atom";

    if ($_GET['blog'] == "main") {
        $blogtitle = $settings['blog']['title'];
    } else {
        $blogtitl = $settings['blog']['title'] . " - " . ucwords($_GET['blog']);
    }

    $feedBuilder = AtomFeedBuilder::create()
        ->withTitle($blogtitle)
        ->withAuthor($settings['blog']['author'])
        ->withFeedUrl($settings['blog']['url'] . "/feed/" . $_GET['blog'] . ".atom")
        ->withSiteUrl($settings['blog']['url'])
        ->withDate(new DateTime(date(DATE_ATOM)));

    $articles = scandir($art_dir, 1);

    $amount = 0;

    foreach ($articles as $article) {
        if (strlen($article) >= 3 && substr($article, -3) == ".md") {
            if ($amount == 10) {
                break;
            } else {
                $file = ArticleGenerator::getText($art_dir, $article);
                $text = Parsedown::instance()
                    ->setBreaksEnabled(true)// with linebreaks
                    ->text($file);
                if (new DateTime(date(DATE_ATOM, strtotime($datestring))) != null) {
                    $date = new DateTime(
                        date(
                            DATE_ATOM,
                            strtotime($datestring)
                        )
                    );
                } else {
                    $date = new DateTime(date(DATE_ATOM));
                }
                $date = new DateTime(date(DATE_ATOM));
                $feedBuilder
                    ->withItem(AtomItemBuilder::create($feedBuilder)
                        ->withTitle(
                            ArticleGenerator::getTitle($art_dir, $article)
                        )
                        ->withUrl(
                            $settings['blog']['url'] . "./?article=" . substr($article, 0, strlen($article) - 3)
                        )
                        ->withAuthor(
                            ArticleGenerator::getAuthor($art_dir, $article)
                        )
                        ->withPublishedDate(
                            parseDate(ArticleGenerator::getDate($art_dir, $article))
                        )
                        ->withUpdatedDate(
                            parseDate(ArticleGenerator::getDate($art_dir, $article))
                        )
                        ->withSummary(
                            ArticleGenerator::getSummary($art_dir, $article)
                        )
                        ->withContent($text));
                $amount += 1;
            }
        }
    }


    $feed = $feedBuilder->build();

    $file = fopen($feed_path, "w");

    if (fwrite($file, $feed) === false) {
        echo "-1";
        exit;
    }

    fclose($file);

    echo "0";
}

function parseDate($datestring)
{
    $datetime = new DateTime(date(DATE_ATOM));
    try {
        $datetime = new DateTime(
            date(
                DATE_ATOM,
                strtotime($datestring)
            )
        );
    } catch (Exception $e) {
        $datetime = new DateTime(date(DATE_ATOM));
    }
    return $datetime;
}
