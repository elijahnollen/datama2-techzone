TechZone API view layer.

This project returns JSON responses only. Response rendering is handled by
`sendJson()` in `app/Core/common.php`, while controllers in
`app/Controllers/` decide payload and status codes.
