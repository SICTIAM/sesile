import React, { Component }from 'react'
import PropTypes from 'prop-types'
import Link from 'react-router-dom'
import { translate } from 'react-i18next'
import History from '../_utils/History'
import { escapedValue } from '../_utils/Search'

const { object, array, func, any } = PropTypes

class Groups extends Component {

    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props)
        this.state = {
            collectivites: [],
            currentCollectiviteName: '',
            currentCollectiviteId: null,
            isSuperAdmin: false,
            groups: [],
            filteredGroups: [],
            groupName: '',
            userName: ''
        }
    }

    componentDidMount() {
        const user = this.props.user
        this.setState({currentCollectiviteName: user.collectivite.domain, currentCollectiviteId: user.collectivite.id})

        this.getListGroupe(user.collectivite.id)
        if(user.roles.find(role => role.includes("ROLE_SUPER_ADMIN")) !== undefined) {
            this.getListCollectivite()
            this.setState({isSuperAdmin: true})
        }
    }

    getListCollectivite() {
        fetch(Routing.generate('sesile_main_collectiviteapi_getall'), {credentials: 'same-origin'})
        .then(response => response.json())
        .then(json => this.setState({collectivites: json}))
    }

    getListGroupe(id) {
        fetch(Routing.generate('sesile_user_userpackapi_getbycollectivite', {collectiviteId: id}), {credentials: 'same-origin'})
        .then(response => response.json())
        .then(json => this.setState({groups: json, filteredGroups: json}))
    }

    handleChangeCollectivite = (currentCollectiviteId) => {
        this.setState({currentCollectiviteId, userName: '', groupName: ''})
        this.getListGroupe(currentCollectiviteId)
    }

    handleSearchByUserName = (userName) => {
        this.setState({userName})
        const regex = escapedValue(userName, this.state.filteredGroups, this.state.groups)
        const filteredGroups = this.state.groups.filter(group => regex.test(group.users.map(user => user._nom)))
        this.setState({filteredGroups})
    }

    handleSearchByGroupName = (groupName) => {
        this.setState({groupName})
        const regex = escapedValue(groupName, this.state.filteredGroups, this.state.groups)
        const filteredGroups = this.state.groups.filter(group => regex.test(group.nom))
        this.setState({filteredGroups})
    }
    
    
    render() {
        const { t } = this.context
        const user = this.props.user 
        const { currentCollectiviteName, currentCollectiviteId, collectivites, isSuperAdmin, filteredGroups, groupName, userName } = this.state
        const listFilteredGroups = filteredGroups.map((group, key) => <GroupRow key={key} group={group} collectiviteId={currentCollectiviteId} />)
        return (
            <div className="user-group">
                <h4 className="text-center text-bold">{t('admin.title', {name: t('admin.group.complet_name')})}</h4>
                <p className="text-center">{t('admin.subtitle')}</p>
                <div className="grid-x align-center-middle">
                    <div className="cell medium-6">
                        <div className="grid-x grid-padding-x">
                            <div className="auto cell">
                                <label htmlFor="name-search-admin">{t('admin.label.which')}</label>
                                <input id="name-search-admin"
                                    placeholder={t('admin.placeholder.type_name', {name: t('admin.group.name')})}
                                    type="text" 
                                    value={groupName}
                                    onChange={(e) => this.handleSearchByGroupName(e.target.value)} />
                            </div>
                            <div className="auto cell">
                                <label htmlFor="user-search-admin">{t('admin.label.who')}</label>
                                <input id="user-search-admin"
                                    placeholder={t('admin.placeholder.type_user_name')}
                                    type="text" 
                                    value={userName}
                                    onChange={(e) => this.handleSearchByUserName(e.target.value)}/>
                            </div>
                            {isSuperAdmin &&
                                <div className="auto cell">
                                    <label htmlFor="collectivites_select">{t('admin.label.which_collectivite')}</label>
                                    <SelectCollectivite currentCollectivite={currentCollectiviteName} collectivites={collectivites} handleChange={this.handleChangeCollectivite} />
                                </div>
                            }
                        </div>
                    </div>
                    <div className="cell medium-10 list-admin">
                        <div className="grid-x grid-padding-x panel">
                            <div className="cell medium-12 panel-heading grid-x">
                                <div className="cell medium-4">{t('admin.group.name')}</div>
                                <div className="cell medium-8">{t('admin.associated_users')}</div>
                            </div>
                            {(listFilteredGroups.length > 0) ? listFilteredGroups :
                                <div className="cell medium-12 panel-body">
                                    <div className="text-center">
                                        {t('common.no_results', {name: t('admin.group.name')})}
                                    </div>
                                </div>
                            }
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

Groups.PropTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Groups)

const SelectCollectivite = ({currentCollectivite, collectivites, handleChange}) => {
    const options = collectivites.map((collectivite, key) => {
        if(collectivite.active) { return <option key={key} value={collectivite.id}>{collectivite.domain}</option> }
    })
    return(
        <select id="collectivites_select" value={currentCollectivite.domain} onChange={(e) => handleChange(e.target.value)} >
            {options}
        </select>
    )
}

SelectCollectivite.PropTypes = {
    collectivites: array.isRequired,
    handleChange: func.isRequired,
    currentCollectivite:object.isRequired
}

const GroupRow = ({ group, collectiviteId }) => {
    const arrayNoms = []
    group.users.map(user => arrayNoms.unshift(user._nom))
    return (
        <div className="cell medium-12 panel-body grid-x row-admin" onClick={() => History.push(`${collectiviteId}/groupe/${group.id}`)}>
            <div className="cell medium-4 text-uppercase">
                {group.nom}
            </div>
            <div className="cell medium-8 text-uppercase">
                {arrayNoms.join(' | ')}
            </div>
        </div>
    )
}

GroupRow.propTypes = {
    group: object.isRequired,
    collectiviteId: any.isRequired
}