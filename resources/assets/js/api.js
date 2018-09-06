export default class Api {
    constructor(baseUri) {
        this.base_uri = baseUri;
        this.error = '';
        this.token = document.head.querySelector('meta[name="csrf-token"]');
    }

    async getTorrents() {
        try {
            return await this._get('torrents');
        } catch (e) {
            console.log('Arse');
            return false;
        }
    }

    async getTorrent(id) {
        try {
            return await this._get(`torrent/${id}`);
        } catch (e) {
            this.error = "Could not refresh torrent"
            return false;
        }
    }

    async updateTorrent(torrent) {
        try {
            return await this._post(`torrent/${torrent.id}`, torrent);
        } catch (e) {
            this.error = "Could not update torrent"
            return false;
        }
    }

    async copy(torrentList) {
        try {
            return await this._post('copy', { copies: torrentList })
        } catch (e) {
            return false;
        }
    }

    async refresh() {
        try {
            return await this._get('refresh');
        } catch (e) {
            this.error = "Could not refresh torrents"
            return false;
        }
    }

    async _get(endpoint) {
        try {
            const response = await fetch(`${this.base_uri}${endpoint}`);
            const json = await response.json();
            return json.data;
        } catch (e) {
            throw "Network Error";
        }
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
