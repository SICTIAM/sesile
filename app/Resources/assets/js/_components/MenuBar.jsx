import React, { Component } from 'react'
import renderIf from "render-if";

class MenuBar extends Component {


    constructor(props) {
        super(props);
        this.state = {user: {}};
    }
    /*state = {
        user: {}
    }*/

    componentWillMount() {
        fetch(Routing.generate('isauthenticated_user_api'), { credentials: 'same-origin' })
            .then(response => response.json())
            .then(json => this.setState({user : json}))
    }
    render(){
        return (
            <div className="top-bar">
                <div className="top-bar-left">
                    <ul className="dropdown menu" data-dropdown-menu>
                        <li className="menu-text">Sesile</li>
                    </ul>
                </div>
                <div className="top-bar-right">
                    <ul className="menu">
                        {
                            this.state.user ?
                                <li><a href="/logout"><button type="button" className="button">Déconnexion</button></a></li>
                                : <li><a href="/dashboard"><button type="button" className="button">Connexion</button></a></li>
                        }
                    </ul>
                </div>
            </div>
        )
    }
}

export default MenuBar