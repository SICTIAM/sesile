import React, { Component } from 'react'
import { number, func } from 'prop-types'
import { translate } from 'react-i18next'

class SwitchCollectivite extends Component {

    static contextTypes = {
        t: func 
    }

    static propTypes = {
        currentCollectiviteId: number
    }

    state = {
        collectivites: [],
        currentCollectivite: {},
        redirect: false,
        redirectUrl: {}
    }

    componentDidUpdate() {
        $('.switch-collectivite').foundation()
    }

    render() {
        const { user } = this.props
        let host = window.location.host;
        let protocol = window.location.protocol;
        const collectivities = user.collectivities;
        const currentCollectivityId = user.current_org_id;
        const currentCollectivity = collectivities.find((collectivity) => {
            return currentCollectivityId == collectivity.id
        });
        const listItems = collectivities.map((collectivity) =>
            currentCollectivityId !== collectivity.id &&
            <li>
                <a
                    href={`${protocol}//${collectivity.domain}.${host}/connect/ozwillo`}
                    className="button secondary clear">
                    {collectivity.nom}
                </a>
            </li>
        );
        return (
        <div className="switch-collectivite" data-toggle="switch-collectivite">
            <ul className="dropdown menu align-center" data-dropdown-menu>
                <li>
                    <a href="#" className="button primary hollow user-complete-name">
                        <div className="grid-x align-middle">
                            {currentCollectivity.nom}
                        </div>
                    </a>
                    <ul className="menu">
                        {listItems}
                    </ul>
                </li>
            </ul>
        </div>
        )
    }
}

export default translate(['sesile'])(SwitchCollectivite)