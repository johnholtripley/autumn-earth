function placePlotPlacement() {
    if (plotPlacement.numberOfBlockedTiles == 0) {
        document.removeEventListener("mousemove", UI.movePlotPlacementOverlay, false);
        document.removeEventListener("click", placePlotPlacement, false);
        activeAction = "";
        var mouseTilePosition = getTileCoordsFromScreenPosition(cursorPositionX, cursorPositionY);
        // get the top left corner:
        mouseTilePosition[0] -= (plotPlacement.width / 2);
        mouseTilePosition[1] -= (plotPlacement.length / 2);
        // post to server to create files for this character
        getJSON('/game-world/addPlot.php?width=' + plotPlacement.width + '&height=' + plotPlacement.length + '&tileX=' + mouseTilePosition[0] + '&tileY=' + mouseTilePosition[1] + '&chr=' + characterId, function(data) {

            if (data.success) {

                // remove plot item from inventory:
                removeItemTypeFromInventory(plotPlacement.whichType, 1);
                hero.housing.hasAPlayerHouse = true;
                hero.housing.northWestCornerTileX = mouseTilePosition[0];
                hero.housing.northWestCornerTileY = mouseTilePosition[1];
                hero.housing.southEastCornerTileX = mouseTilePosition[0] + parseInt(plotPlacement.width);
                hero.housing.southEastCornerTileY = mouseTilePosition[1] + parseInt(plotPlacement.length);

                // set the empty tile data for the ground floor:
                hero.housing.draft = [];
                hero.housing.draft[0] = [];
                // show footprint so the player knows it's worked:
                hero.settings.showFootprintInEditMode = true;
                showHousingFootprintCheckbox.checked = true;
                UI.openHousingPanel();
                UI.openHousingConstructionPanel();
            }
        }, function(status) {
            // try again 
            // ######
        });
    } else {
        UI.showNotification("<p>I can't put a plot there</p>");
    }
}



var housingNameSpace = {
    'whichTileActive': '',
    'whichWorldTileActive': '',
    'whichElevationActive': 0,
    'whichDyeColourActive': 0,
    'activeTool': 'paint',
    'mousePosition': [],

    update: function() {
        if (key[12]) {
            // escape - cancel active tile
            document.getElementById('housingTile' + housingNameSpace.whichTileActive).classList.remove('active');
            housingNameSpace.whichTileActive = '';
            housingNameSpace.whichWorldTileActive = '';
            housingNameSpace.activeTool = '';
            for (var i = 0; i < housingConstructionToolButtons.length; i++) {
                housingConstructionToolButtons[i].classList.remove('active');
            }
            key[12] = false;
        }
        if (key[7]) {
            UI.toggleUI();
            key[7] = false;
        }
    },

    worldClickHandler: function(e) {

        // if in bounds of the plot footprint:
        var xDiff = e.pageX - (canvasWidth / 2);
        var yDiff = e.pageY - (canvasHeight / 2);
        var nonIsoCoordX = find2DCoordsX(hero.isox + xDiff, hero.isoy + yDiff);
        var nonIsoCoordY = find2DCoordsY(hero.isox + xDiff, hero.isoy + yDiff);
        var clickWorldTileX = getTileX(nonIsoCoordX);
        var clickWorldTileY = getTileY(nonIsoCoordY);
        if (clickWorldTileX >= hero.housing.northWestCornerTileX) {
            if (clickWorldTileX < hero.housing.southEastCornerTileX) {
                if (clickWorldTileY >= hero.housing.northWestCornerTileY) {
                    if (clickWorldTileY < hero.housing.southEastCornerTileY) {
                        switch (housingNameSpace.activeTool) {
                            case 'paint':
                                if (housingNameSpace.whichTileActive != '') {
                                    var newWallTile = {
                                        "type": parseInt(housingNameSpace.whichTileActive),
                                        "tileX": (clickWorldTileX - hero.housing.northWestCornerTileX),
                                        "tileY": (clickWorldTileY - hero.housing.northWestCornerTileY),
                                        "lockedToPlayerId": characterId
                                    }
                                    if (housingNameSpace.whichDyeColourActive != 0) {
                                        newWallTile.colour = parseInt(housingNameSpace.whichDyeColourActive);
                                    }
                                    // place tile:
                                    hero.housing.draft[housingNameSpace.whichElevationActive].push(newWallTile);
                                }
                                break;
                            case 'remove':
                                console.log("removing");
                                // find items at this tile and remove them:
                                hero.housing.draft[housingNameSpace.whichElevationActive] = hero.housing.draft[housingNameSpace.whichElevationActive].filter(function(currentItemObject) {
                                    return (!((currentItemObject.tileX == (clickWorldTileX - hero.housing.northWestCornerTileX)) && (currentItemObject.tileY == (clickWorldTileY - hero.housing.northWestCornerTileY))));
                                });
                                break
                        }
                    }
                }
            }
        }
    },

    mouseMove: function(e) {
        housingNameSpace.mousePosition = getTileCoordsFromScreenPosition(e.pageX, e.pageY);
    },

    toggleShowPlotFootprint: function(e) {
        if (e.target.checked) {
            hero.settings.showFootprintInEditMode = true;
        } else {
            hero.settings.showFootprintInEditMode = false;
        }

    },

    housingTileColourChange: function(e) {
        if (housingNameSpace.whichDyeColourActive != housingTileColour.value) {
            housingNameSpace.whichDyeColourActive = housingTileColour.value;
            housingNameSpace.loadNewTile();
            // change colour of available tiles:
            var colourSuffix = "";
            if (housingTileColour.value != "0") {
                colourSuffix = '-' + colourNames[housingNameSpace.whichDyeColourActive];
            }
            for (var i = 0; i < housingTileSelectionListItems.length; i++) {
                housingTileSelectionListItems[i].firstElementChild.src = '/images/game-world/items/' + housingTileSelectionListItems[i].getAttribute('data-cleanurl') + colourSuffix + '.png';
            }
        }
    },

    selectNewTile: function(e) {
        if (housingNameSpace.whichTileActive != '') {
            document.getElementById('housingTile' + housingNameSpace.whichTileActive).classList.remove('active');
        }
        var whichTile = getNearestParentId(e.target);
        whichTile.classList.add('active');

        housingNameSpace.whichWorldTileActive = whichTile.getAttribute("data-cleanurl");

        housingNameSpace.whichTileActive = whichTile.getAttribute("data-id");
        housingNameSpace.loadNewTile();
    },

    loadNewTile: function() {
        // load world tile asset if it's not already loaded:
        // check if the wall is being dyed:
        var thisFileColourSuffix = '';
        if (housingNameSpace.whichDyeColourActive != 0) {
            // bypass hasInherent colour checks as won't be in inventory items

            var thisColourName = colourNames[housingNameSpace.whichDyeColourActive];
            if (thisColourName != "") {
                thisFileColourSuffix = "-" + thisColourName.toLowerCase();
            }
        }

        var itemID = "item" + housingNameSpace.whichTileActive + thisFileColourSuffix;

        if (typeof itemImages[itemID] === "undefined") {
            Loader.preload([{ name: itemID, src: '/images/game-world/items/' + housingNameSpace.whichWorldTileActive + thisFileColourSuffix + '.png' }], function() { itemImages[itemID] = Loader.getImage(itemID); }, function() {});
        }
    },

    commitDesign: function() {

        // check money and confirm #####

        // save json to file system:
        getJSONWithParams("/game-world/savePlot.php", 'chr=' + characterId + '&postData=' + JSON.stringify(hero.housing.draft) + '&northWestCornerTileX=' + hero.housing.northWestCornerTileX + '&northWestCornerTileY=' + hero.housing.northWestCornerTileY, function(data) {

            if (data.success) {
                // check no pet, hero, NPC etc in the way - move if so ####


                // add data to local mapData - first, find which maps this plot is over:
                var whichMapsToUpdate = uniqueValues([findWhichWorldMap(hero.housing.northWestCornerTileX, hero.housing.northWestCornerTileY), findWhichWorldMap(hero.housing.southEastCornerTileX, hero.housing.southEastCornerTileY), findWhichWorldMap(hero.housing.southEastCornerTileX, hero.housing.northWestCornerTileY), findWhichWorldMap(hero.housing.northWestCornerTileX, hero.housing.southEastCornerTileY)]);
                // remove existing housing data for this player from these maps:
                for (var i = 0; i < whichMapsToUpdate.length; i++) {
                    // need to check if they're within the plot footprint to be safe?  ###
                    thisMapData[(whichMapsToUpdate[i])].items = thisMapData[(whichMapsToUpdate[i])].items.filter(function(currentItemObject) {
                        return currentItemObject.lockedToPlayerId !== characterId;
                    });
                }

                // loop through housing.draft[0] for the external tiles, and add those to the relevant map
                var clonedHousingItem, whichMap;
                for (var i = 0; i < hero.housing.draft[0].length; i++) {
                    clonedHousingItem = JSON.parse(JSON.stringify(hero.housing.draft[0][i]));
                    // adjust the tile coordinates:
                    clonedHousingItem.tileX += hero.housing.northWestCornerTileX;
                    clonedHousingItem.tileY += hero.housing.northWestCornerTileY;
                    // need to get inventory data for this as well #############
                    whichMap = findWhichWorldMap(clonedHousingItem.tileX, clonedHousingItem.tileY);
                    thisMapData[whichMap].items.push(clonedHousingItem);
                    initialiseItem(thisMapData[whichMap].items[thisMapData[whichMap].items.length - 1]);
                }

                UI.closeHousingConstructionPanel();

            } else {
                // try again? ########
            }
        }, function(status) {
            // try again? ########
        });

    },
    changeActiveTool: function(e) {
        var whichButton = getNearestParentId(e.target);
        housingNameSpace.activeTool = whichButton.getAttribute("data-action");
        for (var i = 0; i < housingConstructionToolButtons.length; i++) {
            housingConstructionToolButtons[i].classList.remove('active');
        }
        whichButton.classList.add('active');
    }
}