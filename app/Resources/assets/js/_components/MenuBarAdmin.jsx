import React, { Component } from 'react'
import {Link} from "react-router-dom";

class MenuBarAdmin extends Component {

    render() {
        return (
            <ul className="menu">
                <li><Link to={"/admin/utilisateurs"} className="button gray btn-user-conf"><span>Utilisateurs</span></Link></li>
                <li><Link to={"/admin/circuits-de-validation"} className="button gray btn-user-conf"><span>Circuit</span></Link></li>
                <li><Link to={"/admin/groupes"} className="button gray btn-user-conf"><span>Groupes</span></Link></li>
                <li><Link to={"/admin/types-classeur"} className="button gray btn-user-conf"><span>Types classeur</span></Link></li>
            </ul>
        )
    }
}

export default MenuBarAdmin