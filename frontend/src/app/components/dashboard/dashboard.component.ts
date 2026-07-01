import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PlayerService } from '../../services/player.service';
import { PlayerDetailComponent } from '../player-detail/player-detail.component';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, PlayerDetailComponent],
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {
  players: any[] = [];
  selectedPlayerId: number | null = null;

  constructor(private playerService: PlayerService) {}

  ngOnInit() { this.loadPlayers(); }

  loadPlayers() {
    this.playerService.getPlayers().subscribe({
      next: (data) => {
        this.players = data.sort((a: any, b: any) =>
          (b.flagged ? 1 : 0) - (a.flagged ? 1 : 0)
        );
      },
      error: (err) => console.error('Failed to load players:', err)
    });
  }

  selectPlayer(id: number) { this.selectedPlayerId = id; }
  closeDetail() { this.selectedPlayerId = null; }
}