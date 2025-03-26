import React from 'react';
import { BrowserRouter as Router } from 'react-router-dom';
import { ReservationList } from './components/ReservationList';

function App() {
  return (
    <Router>
      <div className="App">
        <ReservationList reservations={[]} onCancel={() => {}} loading={false} />
      </div>
    </Router>
  );
}

export default App;
