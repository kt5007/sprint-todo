<!-- User Registration Modal -->
<div class="modal fade" id="userRegistrationModal" tabindex="-1" role="dialog" aria-labelledby="userRegistrationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userRegistrationModalLabel">ユーザー登録</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add your user registration form here -->
                <form method="post" action="{{ route('user.register') }}">
                    @csrf
                    <div class="form-group">
                        <label for="username">ユーザー名</label>
                        <input type="text" class="form-control" name="username" placeholder="アジャイル侍" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>