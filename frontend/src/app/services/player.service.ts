// services/player.service.ts
// Shared HTTP wrapper. providedIn: 'root' = one singleton instance for the whole app.
// Components inject this instead of using HttpClient directly.

import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class PlayerService {

  // :4200 = Angular dev server directly → needs full URL to Nginx on :8080.
  // Otherwise (through Nginx, production) → relative /api works same-origin.
  private apiUrl = window.location.port === '4200'
    ? 'http://localhost:8080/api'
    : '/api';

  constructor(private http: HttpClient) {}

  // Called by DashboardComponent on page load — returns Observable of all players.
  getPlayers(): Observable<any> {
    return this.http.get(`${this.apiUrl}/players.php`);
  }

  // Called by PlayerDetailComponent on row click — returns one player's full detail.
  getPlayer(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/player.php?id=${id}`);
  }
}