export interface Reservation {
    id: number;
    startDate: string;
    endDate: string;
    totalPrice: number;
    status: 'active' | 'cancelled';
    createdAt: string;
    updatedAt: string;
    username?: string;
}

export interface ReservationRequest {
    startDate: string;
    endDate: string;
}
