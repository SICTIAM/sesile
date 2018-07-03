import React, { Component } from 'react'
import {func, object} from 'prop-types'
import {Select} from '../_components/Form'
import { translate } from 'react-i18next'

class HeliosInfos extends Component {

    constructor(props) {
        super(props)
        this.state = {}
    }

    static contextTypes = { t: func }

    componentDidMount () { }

    render () {
        const { t } = this.context
        const { pes, voucher, handleChangeVouchers } = this.props
        const ListVouchers = pes.vouchers.map(option => <option key={option.id} value={option.id}>{option.id}</option>)

        return (
            <div className="cell medium-12" style={{marginBottom: '20px'}}>
                <div className="grid-x align-middle" style={{marginBottom: '10px'}}>
                    <div className="cell medium-3 text-bold">{t('common.helios.voucher')}</div>
                    <Select
                        style={{
                            fontSize: '1.15em',
                            height: '20px',
                            width: '63px',
                            margin: '0',
                            paddingBottom: '0',
                            paddingTop: '0'
                        }}
                        id="vouchers"
                        value={voucher.id}
                        className="medium-6 cell"
                        onChange={handleChangeVouchers}>
                        {ListVouchers}
                    </Select>
                </div>
                <div className="grid-x" style={{marginBottom: '10px'}}>
                    <div className="cell medium-3 text-bold">{t('common.helios.budget')}</div>
                    <div className="cell medium-9">{ pes.budget }</div>
                </div>
                <div className="grid-x">
                    <div className="cell medium-3 text-bold">{t('common.helios.signing')}</div>
                    <div className="cell medium-9">
                        { pes.signatory ? pes.signatory : t('common.helios.no_signing') }
                    </div>
                </div>
            </div>
        )
    }
}

HeliosInfos.propsType = {
    pes: object.isRequired,
    voucher: object.isRequired,
    handleChangeVouchers: func
}

export default translate(['sesile'])(HeliosInfos)