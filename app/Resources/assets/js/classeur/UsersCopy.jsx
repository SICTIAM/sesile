import React, { Component } from 'react'
import {func, number, array, string} from 'prop-types'
import Select from 'react-select'
import { translate } from 'react-i18next'

class UsersCopy extends Component {

    static contextTypes = {
        t: func
    }

    static propTypes = {
        currentCollectiviteId: number,
        handleChange: func,
        className: string,
        users_copy: array
    }

    state = {
        users_collectivite: []
    }

    componentDidMount() {
        this.fetchUsersCollectivite(this.props.currentCollectiviteId)
    }

    fetchUsersCollectivite(id) {
        fetch(Routing.generate('sesile_user_userapi_userscollectiviteselect', {id}) , { credentials: 'same-origin'})
            .then(response => response.json())
            .then(users_collectivite => this.setState({users_collectivite}))
    }

    render () {

        const { handleChange, className, users_copy } = this.props
        const { users_collectivite} = this.state
        const { t } = this.context

        return (
            <div className={className}>
                <label htmlFor="users_copy_select" className="text-bold">{t('common.classeurs.users_copy')}</label>
                <Select id="users_copy_select"
                        value={users_copy}
                        multi
                        placeholder={t('common.classeurs.users_copy_select')}
                        options={users_collectivite}
                        onChange={handleChange}
                />
            </div>
        )
    }
}

export default translate('sesile')(UsersCopy)