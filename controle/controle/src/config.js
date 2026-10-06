// Remote API configuration - update with your server URL
export const REMOTE_API_BASE = 'http://localhost:3000';

// Endpoint that returns an array of objects to sync.
export const REMOTE_DATA_ENDPOINT = `${REMOTE_API_BASE}/all-data`;
// Endpoint that returns pages as JSON array: [{ id, title, content }, ...]
// Full DB dump endpoint (returns an object with multiple arrays by key)
export const REMOTE_FULL_DUMP_ENDPOINT = `${REMOTE_API_BASE}/export-db`;
