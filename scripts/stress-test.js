import http from 'k6/http';
import { check } from 'k6';

export const options = {
  scenarios: {
    concurrent_orders: {
      executor: 'shared-iterations',
      vus: 100,
      iterations: 100,
      maxDuration: '60s',
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.05'],
    http_req_duration: ['p(95)<8000'],
  },
};

export default function () {
  const payload = JSON.stringify({
    user_id: 1,
    product_id: 2,
    quantity: 1,
  });

  const params = {
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
  };

  const res = http.post('http://host.docker.internal:8080/api/orders/place-async', payload, params);

  check(res, {
    'status is 200 or 201': (r) => r.status === 200 || r.status === 201,
    'system did not crash': (r) => r.status < 500,
  });
}