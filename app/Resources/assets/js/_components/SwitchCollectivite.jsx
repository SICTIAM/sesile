import React, { Component } from 'react'
import { number, func, object } from 'prop-types'
import { translate } from 'react-i18next'
import SearchCollectivite from "../admin/SearchCollectivite"

class SwitchCollectivite extends Component {

    static contextTypes = {
        t: func,
        user: object
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

    componentDidMount() {
        $('.switch-collectivite').foundation()
    }
    componentDidUpdate() {
       if (!this.state.currentCollectivite.nom) {
           const collectivities = this.context.user.collectivities
           const currentCollectivityId = this.context.user.current_org_id
           const currentCollectivity = collectivities.find((collectivity) => {
               return currentCollectivityId == collectivity.id
           })
           this.setState({currentCollectivite:currentCollectivity})
       }
    }
    render() {
        return (
            <div className="switch-collectivite text-right" data-toggle="switch-collectivite">
                {this.context.user.collectivities.length > 1 ?
                    <ul className="dropdown menu align-center" data-dropdown-menu data-close-on-click-inside={false} >
                        <li style={{minWidth: '40%'}}>
                            <a href="#"
                               style={{borderColor: this.props.color, color: this.props.color, cursor: 'default', width: '100%', padding: '8px'}}
                               className="button primary hollow user-complete-name text-left">
                                <i className="fa fa-building" style={{marginRight: '5%', fontSize: 'large'}}/>
                                <span>
                                    {this.state.currentCollectivite.nom}
                                </span>
                            </a>
                                <SearchCollectivite currentCollectivite={this.state.currentCollectivite} className="menu" classButton="button secondary clear"/>
                        </li>
                    </ul> :
                    <span style={{borderColor: this.props.color, color: this.props.color, cursor: 'default', minWidth: '40%', padding: '8px'}}
                       className="button primary hollow user-complete-name text-left">
                        <i className="fa fa-building" style={{marginRight: '5%', fontSize: 'large'}}/>
                        <span>
                            {this.state.currentCollectivite.nom}
                        </span>
                    </span>}
            </div>
        )
    }
}

export default translate(['sesile'])(SwitchCollectivite)