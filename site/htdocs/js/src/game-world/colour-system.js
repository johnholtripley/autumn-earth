colourNames = ["",
    "Crimson",
    "Yellow",
    "Orange",
    "Blue",
    "Purple",
    "Green",
    "Brown",
    "White",
    "Pink",
    "(light yellow/cream)",
    "(light orange/coral)",
    "Aquamarine",
    "Violet",
    "(light green/lime)",
    "Tawny",
    "Black",
    "Ruby/Maroon",
    "(dark yellow/amber)",
    "(dark orange/sienna)",
    "(dark blue/sapphire)",
    "(indigo/imperial purple)",
    "(dark green/emerald/olive)",
    "(dark brown/chestnut)",
    "Grey"
];

function mixColours() {
    // use to get the resulting colour from any number of colours passed in.
    // eg. resultingColour = mixColours(4,2,8,16,16,16,16,16,16,16,16,16);
    // display name would then be colourNames[resultingColour]
    var colIndex = 0;
    var amountOfBlack = 0;
    var amountOfWhite = 0;
    var colourQuantities = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    for (var i = 0; i < arguments.length; i++) {
        colourQuantities[(arguments[i])]++;
        colIndex |= arguments[i];
        // check for black and white bit in this colour:
        if (arguments[i] == (16 | arguments[i])) {
            amountOfBlack++;
        }
        if (arguments[i] == (8 | arguments[i])) {
            amountOfWhite++;
        }
    }
    // determine if there was one colour more prevalent than the others - if so, make the output colour this colour:
    for (var i = 0; i < colourQuantities.length; i++) {
        if (colourQuantities[i] / arguments.length > 0.7) {
            colIndex = i;
            break;
        }
    }
    if (colIndex > 24) {
        // colour has both black and white - see if one outweighs the other:
        if (amountOfBlack > amountOfWhite) {
            colIndex -= 8;
        } else if (amountOfBlack < amountOfWhite) {
            colIndex -= 16;
        } else {
            // equal amounts:
            colIndex -= 24;
        }
    }
    return colIndex;
}