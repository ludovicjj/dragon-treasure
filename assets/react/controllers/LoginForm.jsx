import React from 'react';

const Field = React.forwardRef(function (props, ref) {
    const {name, helpText, children} = props

    return <div className="mb-3">
        <label htmlFor={name} className="form-label">{children}</label>
        <input type="text" name={name} id={name} ref={ref} className="form-control"/>
        <div id={name + 'HelpBlock'} className="form-text">
            { helpText }
        </div>
    </div>
})

export default class Home extends React.Component {
    constructor(props) {
        super(props)
        this.handleSubmit = this.handleSubmit.bind(this);
        this.email = React.createRef()
        this.password = React.createRef()
        this.state = {
            error: ''
        }
    }

    async handleSubmit(e) {
        e.preventDefault();
        const email = this.email.current
        const password = this.password.current

        const response = await fetch('/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: email.value,
                password: password.value
            })
        })

        if (!response.ok) {
            const data = await response.json()
            this.setState({
                error: data.error
            })

            return;
        }

        // reset form fields
        this.setState({
            error: ''
        })
        email.value= ''
        password.value= ''
    }

    render () {
        return <form onSubmit={this.handleSubmit}>
            {this.state.error && (
                <div className="alert alert-danger">{this.state.error}</div>
            )}
            <Field name="email" ref={this.email} helpText={'Try: hmarquardt@hotmail.com'}>Email</Field>
            <Field name="password" ref={this.password} helpText={'Try: password'}>Password</Field>
            <button className="btn btn-primary">Submit</button>
        </form>;
    }
}