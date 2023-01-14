import axios from "axios";

const axiosArgs = {
    baseURL: window.StackonetSupportTicket.restRoot,
    headers: {},
};
if (window.StackonetSupportTicket.restNonce) {
    axiosArgs.headers = {'X-WP-Nonce': window.StackonetSupportTicket.restNonce};
}

const http = axios.create(axiosArgs);

export default http;
