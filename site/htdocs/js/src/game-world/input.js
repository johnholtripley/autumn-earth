var Input = {
    init: function() {
        // Set up the keyboard events
        document.addEventListener('keydown', function(e) { Input.changeKey(e.keyCode, 1) });
        document.addEventListener('keyup', function(e) { Input.changeKey(e.keyCode, 0) });
    },

    // called on key up and key down events
    changeKey: function(which, to) {
        switch (which) {
            case KeyBindings.left:
                key[0] = to;
                break;
            case KeyBindings.up:
                key[2] = to;
                break;
            case KeyBindings.right:
                key[1] = to;
                break;
            case KeyBindings.down:
                key[3] = to;
                break;
            case KeyBindings.action:
                key[4] = to;
                break;
        }
    }
}