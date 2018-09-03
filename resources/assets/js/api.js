export default class Api {
    constructor() {
        this.base_uri = "http://localhost/api/";
        this.error = '';
        this.token = document.head.querySelector('meta[name="csrf-token"]');
    }

    getTorrents() {
        try {
            return this._get('torrents').data.data;
        } catch (e) {
            return false;
        }
    }

    getTorrent(id) {
        try {
            return this._get(`torrent/{id}`).data.data;
        } catch (e) {
            return false;
        }
    }

    updateTorrent(torrent) {
        try {
            return this._post(`torrent/{torrent.id}`, torrent).data.data;
        } catch (e) {
            return false;
        }
    }

    copy(torrentList) {
        try {
            return this._post('copy', torrentList)
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

    _get(endpoint) {
        const json = fetch(`{this.base_uri}{endpoint}`)
            .then(result => result.json())
            .then(json => json)
            .catch(error => { this.error = error; console.log(error) });
        return json;
    }

    _post(endpoint, jsonData) {
        const json = fetch(`{this.base_uri}{endpoint}`, {
            method: "post",
            headers: {
                'Accept': 'application/json, text/plain, */*',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.token.content
            },
            body: JSON.stringify(jsonData)
        })
            .then(result => result.json())
            .then(json => json)
            .catch(error => { this.error = error; console.log(error) });
        return json;
    }
}
