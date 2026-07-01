import { Component, Input, Output, EventEmitter, OnChanges } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PlayerService } from '../../services/player.service';

@Component({
  selector: 'app-player-detail',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './player-detail.component.html',
  styleUrls: ['./player-detail.component.css']
})
export class PlayerDetailComponent implements OnChanges {
  @Input() playerId!: number;
  @Output() close = new EventEmitter<void>();
  data: any = null;

  constructor(private playerService: PlayerService) {}

  ngOnChanges() {
    if (this.playerId) {
      this.playerService.getPlayer(this.playerId).subscribe({
        next: (result) => this.data = result,
        error: (err) => console.error('Failed to load player detail:', err)
      });
    }
  }
}