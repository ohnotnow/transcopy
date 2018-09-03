export default class Api {
    constructor(baseUri) {
        this.base_uri = baseUri;
        this.error = '';
        this.token = document.head.querySelector('meta[name="csrf-token"]');
    }

    getTorrents() {
        try {
            return this._get('torrents');
        } catch (e) {
            return false;
        }
    }

    getTorrent(id) {
        try {
            return this._get(`torrent/${id}`);
        } catch (e) {
            return false;
        }
    }

    updateTorrent(torrent) {
        try {
            return this._post(`torrent/${torrent.id}`, torrent);
        } catch (e) {
            return false;
        }
    }

    copy(torrentList) {
        try {
            return this._post('copy', { copies: torrentList })
        } catch (e) {
            return false;
        }
    }

    refresh() {
        try {
            return this._get('refresh');
        } catch (e) {
            return false;
        }
    }

    async _get(endpoint) {
        const response = await fetch(`${this.base_uri}${endpoint}`);
        const json = await response.json();
        return json.data;
    }

    async _post(endpoint, jsonData) {
        const response = await fetch(`${this.base_uri}${endpoint}`, {
            method: "post",
            headers: {
                'Accept': 'application/json, text/plain, */*',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.token.content
            },
            body: JSON.stringify(jsonData)
        });
        const json = await response.json();
        return json.data;
    }
}
