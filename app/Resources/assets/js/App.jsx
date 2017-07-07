import React, { Component } from 'react';


class App extends Component {
    render () {
        return (
            <div>
                <div className="top-bar">
                    <div className="top-bar-left">
                        <ul className="dropdown menu" data-dropdown-menu>
                            <li className="menu-text">Sesile</li>
                        </ul>
                    </div>
                    <div className="top-bar-right">
                        <ul className="menu">
                            <li><a href="/classeur/dashborad"><button type="button" className="button">Connexion</button></a></li>
                        </ul>
                    </div>
                </div>
                <h1 className="text-center">
                    Bienvenue!
                </h1>
            </div>
        )
    }
}

export default App;