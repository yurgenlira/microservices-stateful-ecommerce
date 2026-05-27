import http from 'k6/http';
import { check } from 'k6';

const BASE_URL = __ENV.APP_URL;

export const options = {
    vus: 1,
    duration: '30s',
    thresholds: {
        http_req_failed: ['rate<0.01'],
        http_req_duration: ['p(95)<500'],
    },
};

export default function () {
    const up = http.get(`${BASE_URL}/up`);
    check(up, { '/up returns 200': (r) => r.status === 200 });

    const health = http.get(`${BASE_URL}/api/health`);
    check(health, {
        '/api/health returns 200 or 503': (r) => r.status === 200 || r.status === 503,
        '/api/health has status field': (r) => JSON.parse(r.body).status !== undefined,
    });
}