export interface Reservation {
    id: number;
    startDate: string;
    endDate: string;
    totalPrice: number;
    status: string;
    createdAt: string;
    updatedAt: string;
}

export interface ReservationRequest {
    startDate: string;
    endDate: string;
}
