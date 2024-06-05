<?php
date_default_timezone_set('Europe/Berlin');

$feeds = [
    "Tagesschau" => "https://www.tagesschau.de/xml/rss2/",
    "Focus" => "https://www.focus.de/rssfeed/alle-nachrichten/",
    "FAZ" => "https://www.faz.net/rss/aktuell/",
    "HR" => "https://www.hessenschau.de/index.rss",
    "N-TV" => "https://www.n-tv.de/rss",
    "Welt" => "https://www.welt.de/feeds/topnews.rss",
    "Spiegel" => "https://www.spiegel.de/schlagzeilen/tops/index.rss",
    "Zeit" => "https://newsfeed.zeit.de/all",
    "ZDF" => "https://www.zdf.de/rss/zdf/nachrichten"
];

$articles = [];
foreach ($feeds as $source => $url) {
    $rss = simplexml_load_file($url);
    $count = 0;
    foreach ($rss->channel->item as $item) {
        if ($count >= 5) break;
        $articles[] = [
            'title' => (string)$item->title,
            'link' => (string)$item->link,
            'source' => $source,
            'description' => (string)$item->description,
            'pubDate' => date('d.m.Y H:i', strtotime((string)$item->pubDate))
        ];
        $count++;
    }
}

// Sort articles by publication date (newest first)
usort($articles, function($a, $b) {
    return strtotime($b['pubDate']) - strtotime($a['pubDate']);
});

foreach ($articles as $article) {
    echo '<div class="news-item">';
    echo '<div class="news-content">';
    echo '<div class="news-title">' . $article['title'] . '</div>';
    echo '<div class="news-source">' . $article['source'] . ' - ' . $article['pubDate'] . '</div>';
    echo '<p>' . $article['description'] . '</p>';
    echo '<a href="' . $article['link'] . '" class="news-link" target="_blank">Mehr lesen</a>';
    echo '</div></div>';
}
?>
