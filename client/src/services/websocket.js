import { useState, useEffect, useCallback } from 'react';
import { toast } from 'react-toastify';

const WEBSOCKET_URL = 'ws://localhost:8081';

class WebSocketClient {
    constructor() {
        this.connection = null;
        this.subscribedBusinesses = new Set();
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 1000;
    }

    connect() {
        return new Promise((resolve, reject) => {
            try {
                this.connection = new WebSocket(WEBSOCKET_URL);

                this.connection.onopen = () => {
                    console.log('WebSocket connected');
                    this.reconnectAttempts = 0;
                    resolve();
                };

                this.connection.onclose = () => {
                    console.log('WebSocket disconnected');
                    this.handleReconnect();
                };

                this.connection.onerror = (error) => {
                    console.error('WebSocket error:', error);
                    reject(error);
                };

                this.connection.onmessage = (event) => {
                    this.handleMessage(event);
                };
            } catch (error) {
                reject(error);
            }
        });
    }

    handleReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            setTimeout(() => {
                this.connect();
            }, this.reconnectDelay * this.reconnectAttempts);
        } else {
            toast.error('Failed to connect to WebSocket server');
        }
    }

    subscribe(businessId) {
        if (!this.connection || this.connection.readyState !== WebSocket.OPEN) {
            return Promise.reject(new Error('WebSocket not connected'));
        }

        this.subscribedBusinesses.add(businessId);
        this.connection.send(JSON.stringify({
            type: 'subscribe',
            businessId
        }));
    }

    unsubscribe(businessId) {
        if (!this.connection || this.connection.readyState !== WebSocket.OPEN) {
            return Promise.reject(new Error('WebSocket not connected'));
        }

        this.subscribedBusinesses.delete(businessId);
        this.connection.send(JSON.stringify({
            type: 'unsubscribe',
            businessId
        }));
    }

    handleMessage(event) {
        const data = JSON.parse(event.data);
        
        switch (data.type) {
            case 'success':
                console.log('WebSocket success:', data.message);
                break;
            case 'error':
                console.error('WebSocket error:', data.message);
                break;
            case 'update':
                this.handleUpdate(data);
                break;
            case 'new_review':
                this.handleNewReview(data);
                break;
            default:
                console.log('Unknown message type:', data.type);
        }
    }

    handleUpdate(data) {
        if (this.subscribedBusinesses.has(data.businessId)) {
            // Handle business update
            console.log('Received business update:', data);
        }
    }

    handleNewReview(data) {
        if (this.subscribedBusinesses.has(data.businessId)) {
            // Handle new review
            console.log('Received new review:', data);
        }
    }

    close() {
        if (this.connection) {
            this.connection.close();
        }
    }
}

const useWebSocket = (businessId) => {
    const [isConnected, setIsConnected] = useState(false);
    const [error, setError] = useState(null);
    const [isLoading, setIsLoading] = useState(true);

    const client = useCallback(() => new WebSocketClient(), []);

    useEffect(() => {
        let wsClient = client();

        wsClient.connect()
            .then(() => {
                setIsConnected(true);
                setIsLoading(false);
                if (businessId) {
                    wsClient.subscribe(businessId);
                }
            })
            .catch((err) => {
                setError(err);
                setIsLoading(false);
            });

        return () => {
            wsClient.close();
        };
    }, [businessId, client]);

    return {
        isConnected,
        isLoading,
        error,
        subscribe: (id) => client().subscribe(id),
        unsubscribe: (id) => client().unsubscribe(id)
    };
};

export { useWebSocket, WebSocketClient };
export default WebSocketClient;
