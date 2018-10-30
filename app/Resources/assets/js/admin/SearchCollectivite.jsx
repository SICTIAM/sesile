import React, { Component } from 'react'
import { translate } from 'react-i18next'
import {escapedValue} from "../_utils/Search"
import { object, func } from 'prop-types'


class SearchCollectivite extends Component {
    static contextTypes = {
        t: func
    }
    state = {
        collectivite: false,
        search: '',
        collectivites: [],
        filteredCollectivites: [],
        filteredDisplay: []
    }

    componentWillReceiveProps(nextProps) {
        if (nextProps.collectivite.length > 0) {
            const collectivity = nextProps.collectivite
            if (nextProps.currentCollectivite) {
                const currentCollectiviteIndex = collectivity.indexOf(nextProps.currentCollectivite)
                collectivity.splice(currentCollectiviteIndex, 1);
            }
            this.setState({
                collectivites: nextProps.collectivite,
                filteredCollectivites: nextProps.collectivite,
                collectivite: true,
                filteredDisplay: collectivity.slice(0, 5)
            })
        }
    }

    handleSearch = (value) => {
        this.setState({search: value})
        const regex = escapedValue(value, this.state.filteredCollectivites, this.state.groups)
        const filteredCollectivites = this.state.collectivites.filter(collectivite => regex.test(collectivite.nom))
        this.setState({filteredCollectivites, filteredDisplay: filteredCollectivites.slice(0, 5)})
    }

    render() {
        const {t} = this.context
        const collectiviteList = this.state.filteredDisplay.map((collectivite, key) => <li
                key={key + collectivite.id.toString()}
                style={{minWidth: '40%'}}>
                {this.props.classButton ?
                    <a
                        href={Routing.generate("sesile_main_default_redirecttosubdomain", {subdomain: collectivite.domain})}
                        className={this.props.classButton}>{collectivite.nom}</a>
                    :
                    collectivite.nom
                }
            </li>
        )
        return (
            <ul className={this.props.className} style={{marginBottom: "0.5em"}}>
                {this.state.collectivites.length > 5 &&
                <li style={{listStyle: "none"}}>
                    <input
                        className="input-group-field"
                        placeholder={t('admin.collectivite.title')}
                        type="text"
                        value={this.state.search}
                        onChange={(e) => this.handleSearch(e.target.value)}
                        style={{margin: "0.5em", borderRadius: "5px", fontSize: "0.8em ", maxWidth: "400px"}}
                    />
                </li>
                }
                {this.state.collectivite && collectiviteList}
            </ul>
        )
    }
}

export default translate(['sesile'])(SearchCollectivite)