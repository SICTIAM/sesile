import React, { Component } from 'react'
import {translate} from "react-i18next"
import {escapedValue} from "../_utils/Search";

class SearchCollectivite extends Component {
    state = {
        collectivite: false,
        alotcall: false,
        search: '',
        collectivites: [],
        filteredCollectivites: [],
        filteredDisplay: []
    }

    componentWillReceiveProps(nextProps) {
        if (nextProps.collectivite.length > 0) {
            const collectivite = nextProps.collectivite
            this.setState({collectivites: nextProps.collectivite})
            this.setState({filteredCollectivites: nextProps.collectivite})
            this.isMoreThanFive(nextProps)
            this.setState({collectivite: true})
            this.setState({filteredDisplay: collectivite.slice(0, 5)})
        }
    }

    isMoreThanFive(nextProps) {
        if (nextProps.collectivite.length > 5) {
            this.setState({alotcall: true})
        }
    }

    handleSearch = (value) => {
        this.setState({search: value})
        const regex = escapedValue(value, this.state.filteredCollectivites, this.state.groups)
        const filteredCollectivites = this.state.collectivites.filter(collectivite => regex.test(collectivite.nom))
        this.setState({filteredCollectivites})
        this.setState({filteredDisplay: this.removeElements(filteredCollectivites, 5)})
    }

    render() {
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
            <ul className={this.props.className}>
                {this.state.alotcall &&
                <li style={{listStyle:"none"}}>
                    <input
                        value={this.state.search}
                        onChange={(e) => this.handleSearch(e.target.value)}
                    />
                </li>
                }
                {this.state.collectivite && collectiviteList}
            </ul>
        )
    }
}

export default translate(['sesile'])(SearchCollectivite)