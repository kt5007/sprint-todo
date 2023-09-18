<!-- Sprint Registration Modal -->
<div class="modal fade" id="sprintRegistrationModal" tabindex="-1" role="dialog" aria-labelledby="sprintRegistrationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sprintRegistrationModalLabel">スプリント登録</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add your sprint registration form here -->
                <form method="post" action="{{ route('sprint.register') }}">
                    @csrf
                    <div class="form-group">
                        <label for="start_date">開始日</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">終了日</label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>
                    <div class="mt-2">
                        次のメンバーでスプリントが作成されます。<br>
                        @foreach($active_users as $active_user)
                            {{ $active_user->name . ' ' }}
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                        <button type="submit" class="btn btn-primary">登録</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>