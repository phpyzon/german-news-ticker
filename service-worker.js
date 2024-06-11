self.addEventListener("install", (event) => {
  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  console.log("Service worker activated.");
  self.clients.claim();
});

self.addEventListener("sync", function (event) {
  if (event.tag === "news-fetch") {
    event.waitUntil(fetchNewsAndNotify());
  } else if (event.tag === "sport-news-fetch") {
    event.waitUntil(fetchSportNewsAndNotify());
  }
});

async function fetchNewsAndNotify() {
  const response = await fetch("news_fetcher.php");
  const text = await response.text();
  const parser = new DOMParser();
  const doc = parser.parseFromString(text, "text/html");

  const newTitles = Array.from(doc.getElementsByClassName("news-title")).map(
    (el) => el.innerText
  );
  const oldTitles = await getOldTitles();

  const newArticles = newTitles.filter((title) => !oldTitles.includes(title));

  if (newArticles.length > 0) {
    await setOldTitles(newTitles);

    newArticles.forEach((title) => {
      self.registration.showNotification("Neue Nachricht", {
        body: title,
        icon: "icon.png", // Add your icon here
        tag: title,
      });
    });
  }
}

async function fetchSportNewsAndNotify() {
  const response = await fetch("sportnews_fetcher.php");
  const text = await response.text();
  const parser = new DOMParser();
  const doc = parser.parseFromString(text, "text/html");

  const newTitles = Array.from(doc.getElementsByClassName("news-title")).map(
    (el) => el.innerText
  );
  const oldTitles = await getOldTitles("sport");

  const newArticles = newTitles.filter((title) => !oldTitles.includes(title));

  if (newArticles.length > 0) {
    await setOldTitles(newTitles, "sport");

    newArticles.forEach((title) => {
      self.registration.showNotification("Neue Sportnachricht", {
        body: title,
        icon: "icon.png", // Add your icon here
        tag: title,
      });
    });
  }
}

function getOldTitles(type = "news") {
  return self.clients.matchAll().then((clients) => {
    const client = clients.find(
      (client) => client.visibilityState === "visible"
    );
    if (client) {
      return new Promise((resolve) => {
        const messageChannel = new MessageChannel();
        messageChannel.port1.onmessage = (event) =>
          resolve(event.data[`${type}_titles`]);
        client.postMessage({ type: `get-${type}-titles` }, [
          messageChannel.port2,
        ]);
      });
    } else {
      return [];
    }
  });
}

function setOldTitles(titles, type = "news") {
  return self.clients.matchAll().then((clients) => {
    clients.forEach((client) =>
      client.postMessage({ type: `set-${type}-titles`, titles })
    );
  });
}
