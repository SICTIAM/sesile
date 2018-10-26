import React, { Component } from 'react'
import { number, func } from 'prop-types'
import { translate } from 'react-i18next'
import { extractRootDomain } from './../_utils/Utils'
import SearchCollectivite from "../admin/SearchCollectivite";

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
        const { user } = this.props;
        const collectivities = user.collectivities;
        const currentCollectivityId = user.current_org_id;
        const currentCollectivity = collectivities.find((collectivity) => {
            return currentCollectivityId == collectivity.id
        });
        const listItems = collectivities.map((collectivity, key) =>
            currentCollectivityId !== collectivity.id &&
                <li key={key} style={{minWidth: '40%'}}>
                    <a
                        href={Routing.generate("sesile_main_default_redirecttosubdomain", {subdomain: collectivity.domain})}
                        className="button secondary clear">
                        {collectivity.nom}
                    </a>
                </li>)
        return (
        <div className="switch-collectivite text-right" data-toggle="switch-collectivite">
            {collectivities.length > 1 ?
                <ul className="dropdown menu align-center" data-dropdown-menu data-close-on-click-inside={false} >
                    <li style={{minWidth: '40%'}}>
                        <a href="#"
                           style={{borderColor: this.props.color, color: this.props.color, cursor: 'default', width: '100%', padding: '8px'}}
                           className="button primary hollow user-complete-name text-left">
                            <i className="fa fa-building" style={{marginRight: '5%', fontSize: 'large'}}/>
                            <span>
                                {currentCollectivity.nom}
                            </span>
                        </a>
                            <SearchCollectivite collectivite={collectivities} currentCollectivite={currentCollectivity} className="menu" classButton="button secondary clear"/>
                    </li>
                </ul> :
                <span style={{borderColor: this.props.color, color: this.props.color, cursor: 'default', minWidth: '40%', padding: '8px'}}
                   className="button primary hollow user-complete-name text-left">
                    <i className="fa fa-building" style={{marginRight: '5%', fontSize: 'large'}}/>
                    <span>
                        {currentCollectivity.nom}
                    </span>
                </span>}
        </div>
        )
    }
}

export default translate(['sesile'])(SwitchCollectivite)