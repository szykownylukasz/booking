# Booking Symfony API platform Project

A small programming sample using the Symfony API platform

## Requirements

- **Docker**: Make sure you have the latest version of Docker installed.
- **Docker Compose**: Make sure you have the latest version of Docker Compose installed.

## Installation

1. **Cloning a repository:**

   ```bash
   git clone https://github.com/szykownylukasz/booking.git
   cd booking
   ```
2. **Running the docker:**
   ```bash
   docker-compose -p booking_api up --build -d
   ```
3. **Api visibility:**  
  
   By default the API is available on port 81. When docker is started, composer packages are built, so when you first start it, you need to wait a moment for them to be built and once that happens, the server should work properly.
   It shouldn't take more than a minute
4. **Frontend visibility:**  
  
   By default the frontend is available on port 3000.
5. **API documentation is available at the link:** 
   
	http://localhost:81/api/doc
	
	When using the API, you use the API, use content-type: application/ld+json or application/json. For response you can set also application/ld+json or application/json.

6. **Test users:**  
  
   Admin user:  
&nbsp;&nbsp;&nbsp;&nbsp;l:admin, p:admin  
  
   Normal users:  
&nbsp;&nbsp;&nbsp;&nbsp;User1: l:user1, p:user1  
&nbsp;&nbsp;&nbsp;&nbsp;User2: l:user2, p:user2  