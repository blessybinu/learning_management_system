let cachedStorage;

function getStorage() {
    if (!cachedStorage) {
        cachedStorage = createStore('meta_data', 'client');
    }
    return cachedStorage;
}

function getReqPromise(request) {
    return new Promise((resolve, reject) => {
        request.oncomplete = request.onsuccess = () => resolve(request.result);
        request.onabort = request.onerror = () => reject(request.error);
    });
}

function createStore(dbName, storeName) {
    const request = indexedDB.open(dbName);
    request.onupgradeneeded = () => request.result.createObjectStore(storeName);
    const dbp = getReqPromise(request);
    return (txMode, callback) => dbp.then((db) => callback(db.transaction(storeName, txMode).objectStore(storeName)));
}

function getData(key, customStore = getStorage()) {
    return customStore('readonly', (store) => getReqPromise(store.get(key)));
}

function setData(key, value, customStore = getStorage()) {
    return customStore('readwrite', (store) => {
        store.put(value, key);
        return getReqPromise(store.transaction);
    });
}


self.addEventListener('push', function(event) {
    if(!self.registration) {
        console.warn('Notification not Registered.');
        return;
    }

    var payload = event.data.json();

    getData('client_id').then(client_id => {
        getData('browser_key').then(browser_key => {

            var invalid_client = !payload.client_id || !client_id || payload.client_id != client_id;
            var invalid_browser = !payload.browser_key || !browser_key || payload.browser_key != browser_key
            
            if(invalid_browser || invalid_client) {
                return;
            }

            self.registration.showNotification(payload.title, payload);
        });
    });
});

self.addEventListener('notificationclick', function(e) {
    
    if(e.notification.data.url) {
        // Open the specified URL on notification click
        clients.openWindow(e.notification.data.url);
    }

    e.notification.close();
});

self.addEventListener('message', function(event) {
    var data = JSON.parse(event.data);
    setData('client_id', data.client_id).then(() => {}).catch((err) => console.warn('Client ID saving failed!', err));
    setData('browser_key', data.browser_key).then(() => {}).catch((err) => console.warn('Browser ID saving failed!', err));
});