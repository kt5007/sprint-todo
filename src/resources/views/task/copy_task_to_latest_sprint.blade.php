<!-- Modal -->
<form action="/task/copy" method="post" id="copy-task-form">
    @csrf
    <div class="modal fade" id="task-copy" tabindex="-1" aria-labelledby="task-copy-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="task-copy-label">タスクの複製</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="copy-message">最新のスプリントに複製しますか。</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-primary">複製</button>
                </div>
            </div>
        </div>
    </div>
</form>