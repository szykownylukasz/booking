export interface Reservation {
    id: number;
    startDate: string;
    endDate: string;
    totalPrice: number;
    status: 'active' | 'cancelled';
    createdAt: string;
    updatedAt: string;
    user?: {
        id: number;
        username: string;
    };
    userUsername?: string;
}

export interface ReservationRequest {
    startDate: string;
    endDate: string;
}
